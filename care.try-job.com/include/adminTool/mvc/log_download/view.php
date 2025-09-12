<?php

	//★クラス //

	class Applog_downloadView extends AppBaseView //
	{
		//■処理 //

		function drawDownloadHeader( $iModel ) //
		{
			header( 'Cache-Control: public' );
			header( 'Pragma:' );
	        header( 'Content-Disposition: attachment; filename="' . 'download.log' . '"' );
			header( 'Content-type: application/x-octet-stream; name="' . 'download.log' . '"; charset=Shift_JIS' );

			$fp = fopen( $_POST[ 'file' ] , 'rb' );

			while( $line = fgets( $fp ) )
				{ print $line; }
		}

		function drawFileSelectForm( $iModel ) //
			{ include_once $this->templatePath . 'common/downloadSelectForm.html'; }

		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			global $TOOL_TEMPLATE_PATH;

			$this->templatePath = $TOOL_TEMPLATE_PATH;
		}

		//■変数 //
		private $templatePath = ''; ///<テンプレートファイルの格納パス。
	}
