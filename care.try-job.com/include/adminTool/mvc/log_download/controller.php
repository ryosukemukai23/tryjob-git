<?php

	//★クラス //

	class Applog_downloadController extends AppBaseController //
	{
		//■処理 //

		function doDownload() //
			{ $this->view->drawDownloadHeader( $this->model ); }

		function fileSelectForm() //
		{ $this->view->drawFileSelectForm( $this->model ); }

		//■データ取得 //

		static function GetNeedIncludes() //
		{
			global $SQL_MASTER;

			if( 'MySQLDatabase' == $SQL_MASTER ) //MySQLを使用する場合
				{ $path = 'mysql'; }
			else //SQLiteを使用する場合
				{ $path = 'sqlite'; }

			return Array(
				'include/adminTool/lib/' . $path . '/query.php' ,
				'include/adminTool/lib/' . $path . '/queryWriter.php' ,
			);
		}

		//■コンストラクタ・デストラクタ //

		function __construct() //
		{
			if( $_SESSION[ 'loginedAdminTool' ] ) //ログインしている場合
			{
				if( $_POST[ 'file' ] ) //実行指定がある場合
					{ $this->action = 'doDownload'; }
				else //実行指定がない場合
					{ $this->action = 'fileSelectForm'; }
			}
			else //ログインしていない場合
				{ $this->action = 'error'; }

			parent::__construct();
		}
	}
