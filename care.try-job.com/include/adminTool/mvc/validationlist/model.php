<?php

	//★クラス //
	include_once "./include/GUIManager.php";
include_once "./include/ccProc.php";
include_once "./custom/global.php";


	/**
		@brief 既定の管理ツールのテンプレート一覧表示処理のモデル。
	*/
	class AppValidationListModel extends AppBaseModel //
	{
		//■処理 //

		/**
			@brief テンプレートの一覧を取得する。
		*/
		function getValidationList() //
		{
			// チェックリストの関数名とラベルの対応表をコードを解析してつくる
			$checkDataInc = self::analysisCheckDataLabel("include/base/CheckDataBase.php");
			$checkDataCst = self::analysisCheckDataLabel("custom/checkData.php");

			$gm = SystemUtil::getGMforType('system');

			$checkDataLabelList = array_merge( $checkDataInc,$checkDataCst);

			// csv ファイルから チェック対象の行をとってくる
			global $TABLE_NAME;
			sort( $TABLE_NAME );
			$checkTargetList = Array();
			foreach( $TABLE_NAME as $tableName ) //全てのテーブルを処理
			{
				$checkTargetList[$tableName] = Array();
				$csv = new CSV($tableName);
				$columns = $csv->getColumns();

				foreach ($columns as $name => $column) {
					if( array_search( $name , array('id','delete_key')) !== FALSE ){ continue; }
                    if (!empty($column['regist_validation'])) {
                        $checkTargetList[$tableName][$name] =  explode('/', $column['regist_validation']);
                    }
                    if (!empty($column['edit_validation'])) {
						if (isset($checkTargetList[$tableName][$name])) {
							$checkTargetList[$tableName][$name] = array_unique(array_merge( $checkTargetList[$tableName][$name], explode('/', $column['edit_validation'])));
						} else {
							$checkTargetList[$tableName][$name] = explode('/', $column['edit_validation']);
						}
                    }
                }
			}

			$labelList = Array();
		// チェック項目が存在する各テーブルの REGIST_ERROR_DESIGN を取得して、 htmlならスクレイピング、csv なら対象の行が存在するかチェック
			foreach( $checkTargetList as $tableName => $checkTarget ){
				if( is_null($checkTarget) || empty($checkTarget)){ continue;}
				$statement = DB::Query( 'SELECT * FROM template WHERE target_type = "'.$tableName.'" AND label = "REGIST_ERROR_DESIGN" ORDER BY target_type ASC , label ASC' );

				$row = $statement->fetch();
				if($row){
					$file = PathUtil::ModifyTemplateFilePath( $row[ 'file' ] );
					$ext = pathinfo($file, PATHINFO_EXTENSION);

					if( $ext == "html" ){
						//あとで partGetString に渡す為にファイル名だけ保存している
						$labelList[$tableName] = $file;
					}else if( $ext == "csv" ){
						$fp = fopen ( $file , 'r');
						if($fp ===  FALSE ){
							continue;
						}
						while( $tmp = fgetcsv( $fp , 20480 , ',' , '"' ) ){
							$labelList[$tableName][$tmp[0]]=$tmp[1];
						}
					}
				}

			}

			$validationList = Array();
			// checkTagertList を走査して、チェック対象となるテーブルとカラムを拾いながら
			foreach( $checkTargetList as $tableName => $targetTable ){
				$validationList[ $tableName ] = array();;
				if( empty($targetTable)){
					continue;
				}
				foreach( $targetTable as $column => $validations ) {
					foreach ($validations as $validation) {
                        $validationCommands = explode(':', $validation );
						if(strpos( $validationCommands[0], 'Flag' ) !== FALSE) {
							// FLAG だった場合
							switch( $validationCommands[0]){
								case "Flag":
									$validationCommands = array_slice( $validationCommands, 3);
									break;
								case "ChangeFlag":
									$validationCommands = array_slice( $validationCommands, 1);
									break;
								case "NullFlag":
									$validationCommands = array('Null');
									break;
							}
						}
						// checkDataLabelList を見て 存在するべき エラーラレベルの一覧を造り
						if( !is_null($checkDataLabelList[ $validationCommands[0]]) && !empty($checkDataLabelList[ $validationCommands[0]]) ) {
							foreach ($checkDataLabelList[$validationCommands[0]] as $errorLabel) {
								if ($errorLabel == '[name]') {
									$fixErrorLabel = $column;
								} else {
									$fixErrorLabel = $column.$errorLabel;
								}
								// labelList にてらしあわせて存在しているかを確認する
								if( is_array($labelList[$tableName]) ){
									//csv
									$validationList[$tableName][ $fixErrorLabel ] = $labelList[$tableName][$fixErrorLabel];
								}else if(!empty($labelList[$tableName])){
									// html が存在する
									$message = trim( $gm->partGetString( $labelList[$tableName], $fixErrorLabel));
									$validationList[$tableName][ $fixErrorLabel ] = empty($message)? false: $message ;
								}else{
									$validationList[$tableName][ $fixErrorLabel ] = false;
								}
							}
						}
                    }
				}
			}

			$this->validationList = $validationList;

		}

        // チェックリストの関数名とラベルの対応表をコードを解析してつくる
        //function checkUri(  ～ function までの間で    $this->addError($name. '_URI' してる行
		function analysisCheckDataLabel( $file_name ){

			$list = Array();
			$method = null;

			$checkData = file($file_name);
			foreach($checkData as $line){
				if( preg_match( "/function check(\\w+)\\(/", $line, $matches) > 0 ){
					$method = $matches[1];
					$list[$method] = array();
				}else if(preg_match( "/function (\\w+)\\(/", $line, $matches) > 0){
					$method = null;
				}else if(preg_match( '/\\$this\\-\\>addError\\(.*\\\'(\\w+)\\\'/', $line, $matches) > 0 && $method !== null ) {
					$list[$method][] = $matches[1];
					$list[$method] = array_unique($list[$method]);
				}
			}

			foreach( $list as $name => $set){
				// 空の時
				if(!count($set)) {
					if (preg_match('/null/i', $name) > 0) {
						// Null 系で空の時は矯正で [name] をいれる
						$list[$name] = array("[name]");
					}else if (preg_match('/flag/i', $name) > 0) {
						// Flag 系の時は消す
						unset($list[$name]);
					}
				}
			}
			return $list;
		}

		var $validationList = Array(); ///<テンプレート配列。
	}
