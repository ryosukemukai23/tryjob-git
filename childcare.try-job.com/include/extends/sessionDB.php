<?php

	//テーブル定義

	$EDIT_TYPE                            = 'sessionDB';
	$TABLE_NAME[]                         = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ] = false;
	$THIS_TABLE_IS_NOHTML[ $EDIT_TYPE ]   = false;
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]        = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]     = null;
	$LOGIN_PASSWD_COLUM2[ $EDIT_TYPE ]    = null;
	$LST[ $EDIT_TYPE ]                    = 'system/sessionDB.csv';
	$TDB[ $EDIT_TYPE ]                    = 'system/sessionDB.csv';
	$ID_HEADER[ $EDIT_TYPE ]              = '';
	$ID_LENGTH[ $EDIT_TYPE ]              = 0;

	if( PHP_VERSION_ID < 50400 )
	{
		//PHP5.4未満の場合はインターフェースを定義する

		interface SessionHandlerInterface
		{
			public function open( $savePath , $sessionName );
			public function close();
			public function read( $id );
			public function write( $id , $data );
			public function destroy( $id );
			public function gc( $maxlifetime );
		}
	}

	class sessionDB implements SessionHandlerInterface
	{
		//■変数 //

		private $type = 'sessionDB';
		private $connection;
		private $dbMaster;

		//■処理 //

		public function setDbDetails( $dbMaster , $dbHost , $dbUser , $dbPassword , $dbDatabase )
		{
			global $sqlite_db_path;

			if( 'MySQLDatabase' == $dbMaster ) //MySQL接続の場合
			{
				$this->connection = mysqli_connect( $dbHost , $dbUser , $dbPassword );

				if( !$this->connection ) //接続できない場合
					{ throw new InternalErrorException( "SQLDatabase() : DB CONNECT ERROR. -> mysqli_connect( " . $dbHost . " )\n" ); }

				if( !mysqli_select_db( $this->connection , $dbDatabase ) ) //DBが選択できない場合
					{ throw new InternalErrorException("SQLDatabase() : DB CONNECT ERROR. -> mysqli_select_db( " . $dbDatabase . " )\n"); }
			}
			else //SQLite接続の場合
			{
				$this->connection = new SQLite3( $sqlite_db_path . $dbDatabase . ".session.db" );

				$this->connection->query( 'CREATE TABLE IF NOT EXISTS ' . $this->type . ' ( shadow_id int , delete_flag boolean , id string , data string , timestamp int )' );
			}

			$this->dbMaster = $dbMaster;
		}

		public function open( $sevePath , $sessionName )
		{
			global $SQL_MASTER , $SQL_SERVER , $SQL_ID , $SQL_PASS , $DB_NAME;

			$this->setDbDetails( $SQL_MASTER , $SQL_SERVER , $SQL_ID , $SQL_PASS , $DB_NAME );

			$limit = time() - ( 3600 * 24 );
			$sql   = sprintf( "DELETE FROM %s WHERE timestamp < %s" , $this->type , $limit );

			return ( $this->query( $sql ) ? true : false );
		}

		public function close()
		{
			if( 'MySQLDatabase' == $this->dbMaster ) //MySQL接続の場合
				{ return mysqli_close( $this->connection ); }

			return true;
		}

		public function read( $id )
		{
			$escapeID = $this->escape( $id );
			$sql      = sprintf( "SELECT data FROM %s WHERE id = '%s'" , $this->type , $escapeID );

			if( $result = $this->query( $sql ) ) //SQLの戻り値がある場合
			{
				$record = $this->fetch( $result );

				if( $record ) //レコードが取得できた場合
					{ return $record[ 'data' ]; }
			}

			return '';
		}

		public function write( $id , $data )
		{
			$escapeID   = $this->escape( $id );
			$escapeData = $this->escape( $data );
			$sql        = sprintf( "REPLACE INTO %s VALUES(NULL,NULL,'%s', '%s', '%s')" , $this->type , $escapeID , $escapeData , time() );

			return ( $this->query( $sql ) ? true : false );
		}

		public function destroy( $id )
		{
			return true;

			$escapeID = $this->escape( $id );
			$sql      = sprintf( "DELETE FROM %s WHERE `id` = '%s'" , $this->type , $escapeID );

			return ( $this->query( $sql ) ? true : false );
		}

		public function gc( $max )
		{
			$interval = time() - intval( $max );

			$sql = sprintf( "DELETE FROM %s WHERE `timestamp` < '%s'" , $this->type , $interval );

			return ( $this->query( $sql ) ? true : false );
		}

		public function shutdown()
			{ session_write_close(); }

		public function escape( $value )
		{
			if( 'MySQLDatabase' == $this->dbMaster ) //MySQL接続の場合
				{ return mysqli_real_escape_string( $this->connection , $value ); }
			else //SQLite接続の場合
				{ return $this->connection->escapeString( $value ); }
		}

		public function query( $query )
		{
			if( 'MySQLDatabase' == $this->dbMaster ) //MySQL接続の場合
				{ return mysqli_query( $this->connection , $query ); }
			else //SQLite接続の場合
				{ return $this->connection->query( $query ); }
		}

		public function fetch( $result )
		{
			if( 'MySQLDatabase' == $this->dbMaster ) //MySQL接続の場合
			{
				if( mysqli_num_rows( $result ) ) //1件以上取得できた場合
					{ return mysqli_fetch_assoc( $result ); }
			}
			else //SQLite接続の場合
				{ return $result->fetchArray( SQLITE3_ASSOC ); }
		}
	}

	if( $CONFIG_SQL_DATABASE_SESSION ) //DBによるセッション管理が有効な場合
	{
		$session = new sessionDB();

		if( PHP_VERSION_ID >= 50400 ) //5.4以上の場合
		{
			session_set_save_handler( $session );
			register_shutdown_function( array( $session , 'shutdown' ) );
		}
		else //5.4未満の場合
		{
			session_set_save_handler(
				array( $session , 'open' ) ,
				array( $session , 'close' ) ,
				array( $session , 'read' ) ,
				array( $session , 'write' ) ,
				array( $session , 'destroy' ) ,
				array( $session , 'gc' )
			);

			register_shutdown_function( 'session_write_close' );
		}
	}
