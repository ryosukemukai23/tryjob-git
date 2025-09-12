<?php

	//★クラス //

	/**
		@brief 既定の汎用データ登録フォームのコントローラ。
	*/
	class AppRegisterController extends AppBaseController //
	{
		//■処理 //

		/**
			@brief 登録フォーム表示要求への応答。
		*/
		function registerForm() //
		{
			$this->model->initializeQuery();

			$this->view->drawRegisterFormPage( $this->model );
		}

		/**
			@brief 前画面に戻る要求への応答。
		*/
		function goBack() //
		{
			$this->model->updateQuery();
			$this->model->verifyToken();
			$this->model->goBack();

			$this->view->drawRegisterFormPage( $this->model );
		}

		/**
			@brief 次画面に進む要求への応答。
		*/
		function goForward() //
		{
			$this->model->updateQuery();
			$this->model->verifyToken();
			$this->model->verifyInput();
			$this->model->goForward();

			$this->view->drawRegisterFormPage( $this->model );
		}

		/**
			@brief 編集画面に進む要求への応答。
		*/
		function goEdit() //
			{ $this->view->drawEditRedirectPage( $this->model ); }

		/**
			@brief 登録内容の確認要求への応答。
		*/
		function confirmRegister() //
		{
			$this->model->updateQuery();
			$this->model->verifyToken();
			$this->model->verifyInput();
			$this->model->goForward();

			if( $this->model->canRegister() ) //登録可能な場合
			{
				$this->model->doConfirm();
				$this->view->drawConfirmRegisterPage( $this->model );
			}
			else //登録可能でない場合
				{ $this->view->drawRegisterFormPage( $this->model ); }
		}

		/**
			@brief 登録実行要求への応答。
		*/
		function doRegister() //
		{
			$this->model->updateQuery();
			$this->model->verifyToken();
			$this->model->verifyInput();
			$this->model->doRegister();

			if( $this->model->succeededRegister() ) //登録に成功した場合
				{ $this->view->drawSucceededRegisterPage( $this->model ); }
			else //登録に失敗した場合
				{ $this->view->drawFailedRegisterPage( $this->model ); }
		}

		/**
			@brief 登録件数上限エラー表示要求への応答。
		*/
		function registerMaxCountOver() //
			{ $this->view->drawRegisterMaxCountOverPage( $this->model ); }

		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
		*/
		public function __construct() //
		{
			global $gm;

			ConceptSystem::CheckPostMaxSizeOrver()->OrThrow( 'PostMaxSizeOrver' );
			ConceptSystem::IsNotNull( $_GET[ 'type' ] )->OrThrow( 'InvalidQuery' );
			ConceptSystem::CheckType()->OrThrow( 'InvalidQuery' );
			ConceptSystem::CheckTableRegistUser()->OrThrow( 'IllegalAccess' );


			if( !$this->action ) //要求処理が特定されていない場合
			{
				$isOver = SystemUtil::CheckTableRegistCount( $_GET[ 'type' ] );
			
				if( is_string( $isOver ) ) //登録上限1で超過している場合
					{ $this->action = 'goEdit'; }
				else if( $isOver ) //上限2以上で超過している場合
					{ $this->action = 'registerMaxCountOver'; }
				else if( isset( $_GET[ 'mode' ] ) && 'registMaxCountOver' == $_GET[ 'mode' ] ) //エラー表示要求がある場合
					{ $this->action = 'registerMaxCountOver'; }
				else if( isset( $_POST[ 'back' ] ) ) //前画面に戻る要求がある場合
				{ $this->action = 'goBack'; }
			else //要求がない場合
			{
				if( !isset( $_POST[ 'step' ] ) || !strlen( $_POST[ 'step' ] ) || !$_POST[ 'step' ] )
					{ $_POST['step'] = 1; }

				if( isset( $_POST[ 'post' ] ) ) //POSTクエリが送信されている場合
				{
					if( 'register' == $_POST[ 'post' ] ) //登録実行処理が要求されている場合
						{ $this->action = 'doRegister'; }
					else if( !$gm[ $_GET[ 'type' ] ]->maxStep || $gm[ $_GET[ 'type' ] ]->maxStep <= $_POST[ 'step' ] ) //最終手順まで完了している場合
						{ $this->action = 'confirmRegister'; }
					else //最終手順に至っていない場合
						{ $this->action = 'goForward'; }
				}
				else //要求がない場合
					{ $this->action = 'registerForm'; }
				}
			}

			parent::__construct();
		}
	}
