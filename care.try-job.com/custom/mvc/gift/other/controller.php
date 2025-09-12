<?php

	//★クラス //

	/**
		@brief 既定の静的ページのコントローラ。
	*/
	class AppgiftOtherController extends AppBaseController //
	{
		//■処理 //

		/**
			@brief ページ表示要求への応答。
		*/
		function viewPage() //
		{
			$this->model->searchPage();

			if( $this->model->hasSearchResult() ) //ページがある場合
				{ $this->view->drawPage( $this->model ); }
			else //ページがない場合
				{ $this->view->drawErrorPage( $this->model ); }
		}

		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			$_page = null;
			$_key = null;
			if(isset($_GET['page'])){
				$_page = $_GET['page'];
			}
			if(isset($_GET['key'])){
				$_key = $_GET['key'];
			}
			ConceptSystem::IsAnyNotNull( $_page , $_key )->OrThrow( 'InvalidQuery' );
			ConceptSystem::IsAnyNotEmpty( $_page , $_key )->OrThrow( 'InvalidQuery' );

			Concept::IsTrue(Conf::checkData("job", "nobody_apply", "on"))->OrThrow("IllegalAccess");
			Concept::IsTrue(Conf::getData("charges", "gift")!="")->OrThrow("IllegalAccess");

			$this->action = 'viewPage';

			parent::__construct();
		}
	}
