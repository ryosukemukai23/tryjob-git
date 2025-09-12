<?php

class viewMode{

	private static $defaultTableName = "mid";

	static function checkView(){
		global $gm;
		global $loginUserType;
		global $LOGIN_ID;
		global $LOGIN_TYPE;

		if($loginUserType=='nUser'){
			if(isset($_GET['type'])){
				if(in_array($_GET['type'],array('mid','fresh'))){
					self::setViewMode($_GET['type']);
				}
			}

			if(!in_array(self::getViewMode(),array('mid','fresh'))){
				$db		 = $gm[ $LOGIN_TYPE ]->getDB();
				$table = $db->getTable();
				$table	 = $db->searchTable( $table, 'id', '=', $LOGIN_ID );
				$rec = $db->getFirstRecord($table);

				if($db->getData($rec,"view_mode"))	$mode = $db->getData($rec,"view_mode");
				else {
					$jobConf = Conf::getData("job","type_check");
					if($jobConf == "fresh") {
						$mode = "fresh";
					}else{
						$mode = self::$defaultTableName;
					}
				}
				self::setViewMode($mode);
			}
		}elseif($loginUserType=="nobody"){
			if(isset($_GET['type'])){
				if(in_array($_GET['type'],array('mid','fresh'))){
					self::setViewMode($_GET['type']);
				}
			}

			if(!in_array(self::getViewMode(),array("mid","fresh"))){
				$jobConf = Conf::getData("job","type_check");
				if($jobConf == "fresh"){
					$default = "fresh";
				}else{
					$default = self::$defaultTableName;
				}
				self::setViewMode($default);
			}
		}
	}
	//案件のデフォルト表示切替セット
	static function setViewMode($type){
		global $LOGIN_ID;
		global $loginUserType;
		switch($loginUserType){
			case "nobody":
				$_SESSION[WS_PACKAGE_ID.$LOGIN_ID] = $type;
				break;
			default:
				$_SESSION[$LOGIN_ID] = $type;
				break;
		}
	}
	//案件のデフォルト表示切替ゲット
	static function getViewMode(){
		global $LOGIN_ID;
		global $loginUserType;
		switch($loginUserType){
			case "nobody":
				if(!empty($_SESSION[WS_PACKAGE_ID.$LOGIN_ID]))
					return $_SESSION[WS_PACKAGE_ID.$LOGIN_ID];
				else {
					if(isset($_GET['type'])){
						return $_GET['type'];
					}else{
						return '';
					}
				}
				break;
			default:
				if(isset($_SESSION[$LOGIN_ID])){
					return $_SESSION[$LOGIN_ID];
				}else{
					return '';
				}
				break;
		}
	}
}