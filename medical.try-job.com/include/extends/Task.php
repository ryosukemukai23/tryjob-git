<?php

include_once 'include/base/CommandBase.php';

class Task extends command_base
{
	static $directory	 = 'file/tmp/';
	static $file		 = 'task.txt';
	static $maxTime		 = 900; // 最大ロック時間
	static $maxTry		 = 5; // 最大実行回数


	/**
	 * asyncで実行
	 */
	static function fire( &$gm, $rec, $args )
	{
		$time = self::getLockTime();
		if( $time < time()-self::$maxTime  ) { self::unlock(); $time = 0; }
		if( $time > 0 ) { return; }

		$rec = self::getQueueRecord();
		if( !is_array($rec) ){ return; }

		self::lock();
		$try = self::addTry($rec);

		$activate = 'error';
		if( $try <= self::$maxTry )
		{
			$activate = 'success';

			$db = GMList::getDB('task');

			$class_name = $db->getData( $rec, 'class_name' );
			$method_name = $db->getData( $rec, 'method_name' );
			$argument = $db->getData( $rec, 'argument' );

			$cl = new $class_name();
			$cl->$method_name( self::decodeArgument($argument) );
		}

		self::editStatus( $rec, $activate );
		self::unlock();
	}


	/**
	 * タスクを追加
	 *
	 * @param class_name クラス名
	 * @param method_name 関数名
	 * @param param 引数
	 * @param interval_time 繰返し実行時のインターバル
	 * @param time 実行日時
	 */
	static function push( $class_name, $method_name, $param = "", $interval_time = 0, $time = null  )
	{
		if( !isset($time) ) { $time = time(); }
		$argument = self::encodeArgument( $param );

		if( self::isTask( $class_name, $method_name, $argument ) ) { return; }

		$db = GMList::getDB('task');

		$id	 = (int)SystemUtil::getNewId( $db, 'task' );
		$rec = $db->getNewRecord();
		$db->setData( $rec, 'id',     $id );
		$db->setData( $rec, 'class_name',  $class_name );
		$db->setData( $rec, 'method_name',  $method_name );
		$db->setData( $rec, 'argument', $argument );
		$db->setData( $rec, 'interval_time', $interval_time );
		$db->setData( $rec, 'activate', 0 );
		$db->setData( $rec, 'try', 0 );
		$db->setData( $rec, 'regist', $time );
		
		$db->addRecord($rec);
	}


	/**
	 * 実行する処理レコードを取得
	 *
	 * @return レコード
	 */
	static function getQueueRecord()
	{
		$db = GMList::getDB('task');

		$table = $db->getTable();
		$table = $db->searchTable( $table, 'activate', '=', 0 );
		$table = $db->searchTable( $table, 'regist', '<', time() );
		$table = $db->sortTable( $table, 'regist', 'asc' );
		$rec = $db->getFirstRecord($table);

		return $rec;
	}
	


	/**
	 * 処理待ちのタスクに同じタスクがないか確認
	 *
	 * @param class_name クラス名
	 * @param method_name 関数名
	 * @param param 引数
	 * @return 存在する場合true
	 */
	static function isTask( $class_name, $method_name, $param = "" )
	{
		$argument = self::encodeArgument( $param );

		$db = GMList::getDB('task');

		$table = $db->getTable();
		$table = $db->searchTable( $table, 'class_name', '=', $class_name );
		$table = $db->searchTable( $table, 'method_name', '=', $method_name );
		$table = $db->searchTable( $table, 'argument', '=', $argument );
		$table = $db->searchTable( $table, 'activate', '=', 0 );
		$row = $db->getRow( $table );

		return ( $row > 0 );
	}


	/**
	 * 何度実行しても完了しない処理をスキップするため実行回数をカウント
	 *
	 * @param rec レコード
	 * @return 実行回数
	 */
	static function addTry( &$rec )
	{
		$db = GMList::getDB('task');

		$try = $db->getData( $rec, 'try' )+1;
		$db->setData( $rec, 'try', $try );
		$db->updateRecord( $rec );

		return $try;
	}


	/**
	 * ステータスを変更
	 *
	 * @param rec レコード
	 * @param mode success/error
	 */
	static function editStatus( &$rec, $mode )
	{
		$db = GMList::getDB('task');
		
		switch( $mode )
		{
		case 'success':
			$interval_time = $db->getData($rec, 'interval_time');
			if( $interval_time == 0 )
			{	$db->setData( $rec, 'activate', 4 ); }
			else
			{
				$db->setData( $rec, 'try', 0 );
				$db->setData( $rec, 'regist', time()+$interval_time );
			}
			break;
		case 'error':
			$db->setData( $rec, 'activate', 8 );
			break;
		}

		$db->updateRecord( $rec );
	}


	/**
	 * 引数をレコードに格納するためにエンコード
	 *
	 * @param param 引数配列
	 * @return 文字列化した引数
	 */
	static function encodeArgument( $param = "" )
	{
		$argument = array();
		if( is_array($param) )
		{
			foreach( $param as $index => $value ) { $argument[] = $index.':'.$value;  }
		}

		return implode( '/', $argument );
	}


	/**
	 * 関数を実行するために引数をデコード
	 *
	 * @param argument 文字列化した引数
	 * @return 引数配列
	 */
	static function decodeArgument( $argument = "" )
	{
		$param = array();
		$argument = explode( "/", $argument );
		
		if( !is_array($argument) ) { return $param; }
		foreach( $argument as $arg )
		{
			$tmp = explode( ":", $arg );
			$param[array_shift($tmp)] = array_shift($tmp);
		}

		return $param;
	}


	/**
	 * 複数処理が走らないようロックする
	 *
	 * @retrurn ロックできた場合true
	 */
	static function lock()
	{
		if(!is_dir(self::$directory)) { mkdir( self::$directory, 0777 );SystemUtil::safe_chmod(self::$directory, 0777); }

		$fp = fopen( self::$directory.self::$file, 'wb' );
		if( $fp ) { fclose( $fp ); return true; }

		return false;
	}


	/**
	 * ロックを解除
	 */
	static function unlock()
	{
		if( file_exists(self::$directory.self::$file) ) { unlink(self::$directory.self::$file); }
	}


	/**
	 * ロックされている時間を取得
	 *
	 * @retrurn ロック時間
	 */
	static function getLockTime()
	{
		$time = 0;
		if( file_exists(self::$directory.self::$file) ) { $time = filemtime($file); }

		return $time;
	}

}
?>