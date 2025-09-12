<?php

	//★クラス //

	/**
		@brief 既定の汎用データ登録ページのビュー。
	*/
	class AppRegisterView extends AppBaseView //
	{
		//■処理 //

		/**
			@brief     登録フォームページを出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawRegisterFormPage( $iModel ) //
		{
			global $gm;

			$iModel->gm->setForm( $iModel->rec );
			$iModel->gm->addHiddenForm( 'step' , $_POST[ 'step' ] );
			$iModel->gm->addHiddenForm( 'post' , 'input' );

			foreach( $iModel->gm->colStep as $column => $step ) //全ての手順設定を処理
			{
				if(isset($iModel->step)){
					$mStep = $iModel->step;
				}else{
					$mStep = null;
				}
				if( $step && $mStep != $step ){ //別の手順で入力されている場合
					if(isset($_POST[$column])){
						$val = $_POST[$column];
					}else{
						$val = '';
					}
					$iModel->gm->addHiddenForm($column, $val);
				}
			}

			ob_start();

			$iModel->sys->drawRegistForm( $gm , $iModel->rec , $iModel->loginUserType , $iModel->loginUserRank );

			$this->drawContentsWithHeadFoot( $iModel , ob_get_clean() );
		}

		/**
			@brief     登録内容確認ページを出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawConfirmRegisterPage( $iModel ) //
		{
			global $gm;

			$iModel->gm->setForm( $iModel->rec );
			$iModel->gm->addHiddenForm( 'step' , $_POST[ 'step' ] );
			$iModel->gm->addHiddenForm( 'post' , 'register' );
			$iModel->gm->setHiddenFormRecord( $iModel->rec );

			ob_start();

			$iModel->sys->drawRegistCheck( $gm , $iModel->rec , $iModel->loginUserType , $iModel->loginUserRank );

			$this->drawContentsWithHeadFoot( $iModel , ob_get_clean() );
		}

		/**
			@brief     登録完了ページを出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawSucceededRegisterPage( $iModel ) //
		{
			global $gm;

			ob_start();

			$iModel->sys->drawRegistComp( $gm , $iModel->rec , $iModel->loginUserType , $iModel->loginUserRank );

			$this->drawContentsWithHeadFoot( $iModel , ob_get_clean() );
		}

		/**
			@brief     登録失敗ページを出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawFailedRegisterPage( $iModel ) //
		{
			global $gm;

			ob_start();

			$iModel->sys->drawRegistFaled( $gm , $iModel->loginUserType , $iModel->loginUserRank );

			$this->drawContentsWithHeadFoot( $iModel , ob_get_clean() );
		}

		/**
			@brief     登録件数上限エラーページを出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawRegisterMaxCountOverPage( $iModel ) //
		{
			global $gm;

			ob_start();

			$iModel->sys->drawRegistMaxCountOver( $gm , $iModel->loginUserType , $iModel->loginUserRank );

			$this->drawContentsWithHeadFoot( $iModel , ob_get_clean() );
	}

		/**
			@brief     編集フォームへのリダイレクトページを出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawEditRedirectPage( $iModel ) //
		{
			$id = SystemUtil::CheckTableRegistCount( $iModel->type );

			if( 'index' == WS_SYSTEM_SYSTEM_FORM_ACTON )
				{ header( 'Location: index.php?app_controller=Edit&type=' . $iModel->type . '&id=' . $id ); }
			else
				{ header( 'Location: edit.php?type=' . $iModel->type . '&id=' . $id ); }
		}
	}
