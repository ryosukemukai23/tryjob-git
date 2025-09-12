<?php

// 該当テーブルがわからないユーザーIDからデータを取得するクラス
class User 
{
	static $tableList;
	static $recList;
	static $nameList;
	static $typeList;

	function __construct( $idList = null )
	{
		self::inittableList();
		if( $idList ) { self::initUserData( $idList ); }
	}

	/**
	 * ログインしているユーザーのデータを返す
	 * 
	 * @param col 取得するカラム
	 * @return 取得値
	 */
	static function getLoginData( $col )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $LOGIN_ID;
		// **************************************************************************************

		return self::getData( $LOGIN_ID, $col );
	}


	/**
	 * ユーザーのデータを返す
	 * 
	 * @param id ユーザーID
	 * @param col 取得するカラム
	 * @return 取得値
	 */
	static function getData( $id, $col )
	{
		if( !isset($tableList) ) { self::initTableList(); }
		if( !isset(self::$recList[$id]) ) { self::initUserData( $id ); }

		$db = GMList::getDB( self::$typeList[$id] );
		$data = $db->getData( self::$recList[$id], $col );

		return $data;
	}


	/**
	 * ユーザー名を返す
	 * 
	 * @param id ユーザーID
	 * @return 名前
	 */
	static function getName( $id )
	{
		if( !isset($tableList) ) { self::initTableList(); }
		if( !isset(self::$nameList[$id]) ) { self::initUserData( $id ); }

		return self::$nameList[$id];
	}


	/**
	 * ユーザーの所属するテーブル名を返す
	 * 
	 * @param id ユーザーID
	 * @return テーブル名
	 */
	static function getType( $id )
	{
		if( !isset($tableList) ) { self::inittableList(); }
		if( !isset(self::$typeList[$id]) ) { self::initUserData( $id ); }

		return self::$typeList[$id];
	}


	/**
	 * 複数のユーザー情報を取得する場合getDataの前に先に初期化
	 * 
	 * @param idList ユーザーID配列
	 */
	static function initUserData( $idList )
	{
		if( !isset(self::$tableList) )	 { self::initTableList(); }
		if( !is_array($idList) )		 { $idList = array( $idList ); }

		foreach( self::$tableList as $user )
		{
			$db = GMList::getDB($user);
			$table = $db->getTable();
			$table = $db->searchTable( $table, 'id', 'in', $idList );

			$row = $db->getRow($table);
			for( $i=0; $i<$row; $i++ )
			{
				$rec = $db->getRecord( $table, $i );

				$id = $db->getData( $rec, 'id' );
				self::$nameList[$id] = $db->getData( $rec, 'name' );
				self::$typeList[$id] = $user;
				self::$recList[$id] = $rec;
			}
		}
	}


	/**
	 * 各処理を行う前にユーザーテーブル情報をセット
	 */
	static function initTableList()
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $TABLE_NAME;
		global $THIS_TABLE_IS_USERDATA;
		// **************************************************************************************

		self::$tableList = array();
		foreach( $TABLE_NAME as $name )
		{
			if( $THIS_TABLE_IS_USERDATA[ $name ] ) { self::$tableList[] = $name; }
		}
	}


}
?>