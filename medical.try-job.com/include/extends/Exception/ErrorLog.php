<?php

	/**
		@brief エラーログ管理クラス。
	*/
	class ErrorLog //
	{
		//■ハンドリング //

		/**
			@brief エラーを捕捉してログを出力する。
			@param $iErrorNo     エラー内容を表す数値。
			@param $iErrorString エラー内容文字列。
			@param $iErrorFile   エラーが発生したファイル名。
			@param $iErrorLine   エラーが発生したファイルの行。
		*/
		public static function HandlingError( $iErrorNo , $iErrorString , $iErrorFile , $iErrorLine ) //
		{
			$currentLevel = error_reporting();

			if( 0 == $currentLevel ) //エラー抑制式からのエラーの場合
				{ return false; }

			$exception  = new Exception( $iErrorString );
			$stackTrace = $exception->getTrace();

			array_shift( $stackTrace );

			$errorLog  = self::CreateErrorLogString( $iErrorString , $iErrorFile , $iErrorLine , $stackTrace );

			self::Save( $errorLog );

			$manager = new ErrorManager();
			$manager->ErrorHandler( $iErrorNo , $iErrorString , $iErrorFile , $iErrorLine, null );

			return false;
		}

		/**
			@brief 例外を捕捉してログを出力する。
			@param $iException 例外オブジェクト。
		*/
		public static function HandlingException( $iException ) //
		{
			$errorLog = self::CreateErrorLogString( $iException->getMessage() , $iException->getFile() , $iException->getLine() , $iException->getTrace() );

			self::Save( $errorLog );

			ExceptionManager_ExceptionHandler( $iException );
		}

		/**
			@brief シャットダウン時にエラー情報があればログに出力する。
		*/
		public static function HandlingShutdown() //
		{
			$error = error_get_last(); ///<最後に発生したエラーの情報。

			if( is_null( $error ) ) //エラー情報がない場合
				{ return; }

			switch( $error[ 'type' ] ) //エラーの種別で分岐
			{
				case E_ERROR           : //実行時エラー
				case E_PARSE           : //パースエラー
				case E_CORE_ERROR      : //PHP起動時のエラー
				case E_CORE_WARNING    : //PHP起動時の警告
				case E_COMPILE_ERROR   : //コンパイル時のエラー
				case E_COMPILE_WARNING : //コンパイル時の警告
				{
					self::Save( '[shutdown log] ' . $error[ 'message' ] );
					break;
				}
			}

			$manager = new ErrorManager();
			$manager->ShutdownHandler();
		}

		//■ログ整形 //

		/**
			@brief  例外の情報をエラーログ用の文字列に整形する。
			@param  $iErrorMessage エラー内容。
			@param  $iErrorFile    エラーが発生したファイル名。
			@param  $iErrorLine    エラーが発生したファイルの行。
			@param  $iStackTrace   スタックトレース。
			@return エラーログ文字列。
		*/
		private static function CreateErrorLogString( string $iErrorMessage , string $iErrorFile , int $iErrorLine , array $iStackTrace ) : string //
		{
			$result       = 'error : ' . $iErrorMessage . "\n";
			$result      .= 'on    : ' . $iErrorFile . ' ' . $iErrorLine . "\n";

			if( count( $iStackTrace ) ) //スタックトレースがある場合
			{
				$result      .= 'trace : ' . "\n";
				$lineResults  = array( '[error point]' );

				foreach( $iStackTrace as $line ) //全てのスタックトレースを処理
				{
					$lineString = ( isset( $line[ 'class' ] ) ? $line[ 'class' ] . '::' . $line[ 'function' ] : $line[ 'function' ] );

					if( isset( $line[ 'args' ] ) ) //引数がある場合
					{
						$argsStrings = array();

						foreach( $line[ 'args' ] as $args ) //全ての引数情報を処理
							{ $argsStrings[] = self::ArgsToString( $args ); }

						$lineString .= '( ' . implode( ' , ' , $argsStrings ) . ' )';
					}

					if( isset( $line[ 'file' ] ) ) //ファイル名情報がある場合
						{ $lineString .= "\n\t\t" . ' ... on : ' . $line[ 'file' ] . ' ' . $line[ 'line' ]; }

					$lineResults[] = $lineString;
				}

				$result .= implode( "\n" . '↑' . "\n" , $lineResults );
			}

			return $result;
		}

		/**
			@brief  スタックトレースの引数情報を文字列に変換する。
			@param  $iArgs 引数情報。
			@return 引数文字列。
		*/
		private static function ArgsToString( $iArgs ) : string //
		{
			if( is_object( $iArgs ) ) //オブジェクトの場合
				{ return get_class( $iArgs ); }
			else if( is_array( $iArgs ) ) //配列の場合
			{
				$results = Array();

				foreach( $iArgs as $key => $args ) //全ての値を処理
					{ $results[] = $key . ' => ' . self::ArgsToString( $args ); }

				return '[ ' . implode( ' , ' , $results ) . ' ]';
			}
			else if( is_string( $iArgs ) )
				{ return "'" . $iArgs . "'"; }
			else //その他の値の場合
				{ return ( string )( $iArgs ); }
		}

		//■出力 //

		/**
			@brief ログ文字列をファイルに出力する。
			@param $iErrorString エラーログ文字列。
		*/
		public static function Save( string $iErrorString ) //
		{
			$fp = @fopen( self::$LogFileName , 'ab' );

			if( !$fp ) //ファイルが開けない場合
				{ return false; }

			flock( $fp , LOCK_EX );

			fputs( $fp , '▼ ' . date( 'H時i分s秒' ) . ' のエラーメッセージ' . "\n\n" );
			fputs( $fp , '----------' . "\n\n" );
			fputs( $fp , $iErrorString . "\n\n" );
			fputs( $fp , '----------' . "\n\n" );

			fputs( $fp , '▼リクエスト内容' . "\n\n" );
			fputs( $fp , '----------' . "\n\n" );
			fputs( $fp , 'User IP                    ... ' . $_SERVER[ 'REMOTE_ADDR' ]     . "\n" );
			fputs( $fp , 'User Agent                 ... ' . $_SERVER[ 'HTTP_USER_AGENT' ] . "\n" );
			fputs( $fp , 'Request URI                ... ' . $_SERVER[ 'REQUEST_URI' ]     . "\n" );
			fputs( $fp , 'Working Directory(init)    ... ' . self::$InitCWD                . "\n" );
			fputs( $fp , 'Working Directory(current) ... ' . getcwd()                      . "\n\n" );
			fputs( $fp , '----------' . "\n\n" );
			fputs( $fp , 'GET     ... ' . serialize( $_GET )  . "\n" );
			if(isset($_POST)){	// API で $_POSTが存在しない場合がある。
				fputs( $fp , 'POST    ... ' . serialize( $_POST ) . "\n" );
			}

			if( isset( $_SESSION ) ) //セッションがある場合
				{ fputs( $fp , 'SESSION ... ' . serialize( $_SESSION ) . "\n" ); }
			else
				{ fputs( $fp , 'SESSION ... なし' . "\n" ); }

			fputs( $fp , 'COOKIE  ... ' . serialize( $_COOKIE ) . "\n\n" );
			fputs( $fp , '-----------------------------------------------------' . "\n\n" );
			flock( $fp , LOCK_UN );
			fclose( $fp );
		}

		//■初期化 //

		/**
			@brief  クラスの設定を初期化し、各ハンドラを登録する。
			@return 初期化に成功したか、または既に初期化済みであればtrue。それ以外はfalse。
		*/
		public static function Enable() : bool //
		{
			global $EXCEPTION_CONF;

			if( self::$Initialized ) //既に初期化済みの場合
				{ return true; }

			$logDir            = rtrim( ( 'logs' ) , '/' );
			self::$InitCWD     = getcwd();
			self::$LogFileName = getcwd() . '/' . $logDir . '/' . date( 'Y-m' ) . '.log';

			set_error_handler( Array( 'ErrorLog' , 'HandlingError' ) , $EXCEPTION_CONF[ 'ErrorHandlerLevel' ] );
			set_exception_handler( Array( 'ErrorLog' , 'HandlingException' ) );
			register_shutdown_function( Array( 'ErrorLog' , 'HandlingShutdown' ) );

			self::$Initialized = true;

			return true;
		}

		//■変数 //

		private static $Initialized = false; ///<初期化済みならtrue。
		private static $InitCWD     = null;  ///<初期化時のカレントディレクトリ。
		private static $LogFileName = null;  ///<ログファイルの出力名。
	}

	ErrorLog::Enable();
