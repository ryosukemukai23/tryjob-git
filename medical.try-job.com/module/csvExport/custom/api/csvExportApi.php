<?php

/*
 * 取り込みには次の手順が必要です。
 * 1. uploadCsv() で /file/upload にCSVのアップロード
 * 2. importCsv() で /file/upload のCSVからインポート
 *
 * @author Yuji Noizumi <noizumi@websquare.co.jp>
 */

class mod_csvExportApi {

	/**
	 * ajax でアップロードする
	 * @global type $loginUserType
	 * @param type $param
	 * @return type
	 */
	function uploadCsv($param) {
		global $loginUserType;

		if ($loginUserType != 'admin' && $loginUserType != 'cUser') {
			return;
		}

		$_POST = $param;
		include_once 'custom/head_main.php';

		ConceptSystem::CheckAuthenticityToken()->OrThrow('IllegalTokenAccess');

		set_time_limit(0);

		$result = Array();
		$result['preview'] = '';
		$result['src'] = '';
		$saveFiles = array();

		$directory = 'file/upload/';
		if (!is_dir($directory)) {
			mkdir($directory, 0777, true);
			chmod($directory, 0777);
		} //ディレクトリが存在しない場合は作成

		$replace = '';
		if (isset($_POST['replace'])) {
			$replace = $_POST['replace'];
			if (!preg_match('/' . preg_quote($directory) . '/', $replace)) {
				$replace = '';
			}
		}
		if (!empty($replace)) {
			@unlink($replace);
		}
		foreach ($_FILES as $data) {
			$ext = preg_replace('/^.*\.(.*)$/', '$1', $data['name']);
			$saveName = $directory . time() . '_' . rand() . '.' . $ext;

			if (isset($data['is_big'])) {
				rename($data['tmp_name'], $saveName);
			} else {
				move_uploaded_file($data['tmp_name'], $saveName);
			}

			chmod($saveName,0666);
			$result['src'] = $saveName;
			$result['preview'] .= '';
		}
		$result['token'] = SystemUtil::getAuthenticityToken();
		$result['type'] = $param['type'];

		print json_encode($result);
		return;
	}

	/**
	 * 変換が必要なカラムの設定を返す
	 * 
	 * @return 
	 */
	function getConverter($tableName)
	{
		switch($tableName){
			case 'mid':
			case 'fresh':
				$converter = array(
					'category'=>			array('table'=>'items_type','program'=>'self::name2id','column'=>'id'),
					'work_style'=>			array('table'=>'items_form','program'=>'self::name2id','column'=>'id'),
					'work_place_adds'=>		array('table'=>'adds',		'program'=>'self::name2id','column'=>'id'),
					'work_place_add_sub'=>	array('table'=>'add_sub',	'program'=>'self::name2id','column'=>'id'),
					'traffic1_adds'=>		array('table'=>'adds',		'program'=>'self::name2id','column'=>'id'),
					'traffic1_line'=>		array('table'=>'line',		'program'=>'self::name2id','column'=>'id'),
					'traffic1_station'=>	array('table'=>'station',	'program'=>'self::name2id','column'=>'id'),
					'traffic2_adds'=>		array('table'=>'adds',		'program'=>'self::name2id','column'=>'id'),
					'traffic2_line'=>		array('table'=>'line',		'program'=>'self::name2id','column'=>'id'),
					'traffic2_station'=>	array('table'=>'station',	'program'=>'self::name2id','column'=>'id'),
					'traffic3_adds'=>		array('table'=>'adds',		'program'=>'self::name2id','column'=>'id'),
					'traffic3_line'=>		array('table'=>'line',		'program'=>'self::name2id','column'=>'id'),
					'traffic3_station'=>	array('table'=>'station',	'program'=>'self::name2id','column'=>'id'),
					'traffic4_adds'=>		array('table'=>'adds',		'program'=>'self::name2id','column'=>'id'),
					'traffic4_line'=>		array('table'=>'line',		'program'=>'self::name2id','column'=>'id'),
					'traffic4_station'=>	array('table'=>'station',	'program'=>'self::name2id','column'=>'id'),
					'traffic5_adds'=>		array('table'=>'adds',		'program'=>'self::name2id','column'=>'id'),
					'traffic5_line'=>		array('table'=>'line',		'program'=>'self::name2id','column'=>'id'),
					'traffic5_station'=>	array('table'=>'station',	'program'=>'self::name2id','column'=>'id'),
					'addition'=>			array('table'=>'job_addition','program'=>'self::name2id','column'=>'id'),
					'regist'=>				array('table'=>'Y/m/d H:i:s','program'=>'self::timestamp','column'=>''),
					'edit'=>				array('table'=>'Y/m/d H:i:s','program'=>'self::timestamp','column'=>''),
					'attention_time'=>		array('table'=>'Y/m/d H:i:s','program'=>'self::timestamp','column'=>''),
					'delete_date'=>			array('table'=>'Y/m/d H:i:s','program'=>'self::timestamp','column'=>''),
				);
				break;
			default:
				break;
		}
		return $converter;
	}

	private function getStatusString($line_count,$new_count,$update_count,$delete_count){
		$buf = <<< EOD
{$line_count}件処理しました。<br />
新規：{$new_count} 更新：{$update_count} 削除：{$delete_count}<br />
EOD;
		return $buf;
	}
	/**
	 * csvファイルのインポート処理
	 * 
	 * @global $SYSTEM_CHARACODE
	 * @global $loginUserType
	 * @param $param
	 * @return 
	 */
	function importCsv( $param ){
		global $SYSTEM_CHARACODE;
		global $loginUserType;
		global $LOGIN_ID;
		global $ACTIVE_NONE;
		global $ACTIVE_ACCEPT;
		global $ACTIVE_DENY;

		if ($loginUserType != 'admin' && $loginUserType != 'cUser') {
			return;
		}

		// システム予約
		$system_reserved = array('shadow_id', 'delete_key');
		
		$_POST = $param;
		include_once 'custom/head_main.php';
		ConceptSystem::CheckAuthenticityToken()->OrThrow('IllegalTokenAccess');

		set_time_limit(0);

		$error = false;
		$result['preview'] = '';
		$csvFile = $param['f'];
		$fp = fopen($csvFile, 'rb');
		if ($fp === false) {
			$error = true;
			$result['preview'] .= 'ファイルが開けませんでした<br/>';
		}
		$line_count = 0;
		$new_count = 0;
		$update_count = 0;
		$type = $param['type'];

		// 変換処理用 (DBテーブル名又は日付フォーマット、処理プログラム、データ取得カラム名)
		$converter = self::getConverter($type);

		$db = GMList::getDB($type);

		// fgetcsv用filter 登録
		stream_filter_register(
			'Sjis2utf8Filter', Sjis2utf8FilterLogic::class
		);
		stream_filter_append($fp, 'Sjis2utf8Filter');

		while (!feof($fp) && !$error) { //全ての行を処理
			$data = fgetcsv($fp);

			if (!$data) { //行が空の場合
				continue;
			}
			$line_count++;
			if($line_count == 1){ // 1行目はカラム名を想定
				$tbl_name = array_pop($data);
				if($type != $tbl_name && strtolower($tbl_name) != 'nocheck'){
					$error = true;
					$result['preview'] .= $type.'用のファイルではありません<br/>';
					array_push($data, $tbl_name);
				}
				$colNames = $data;
				continue;
			}else{
				// csvExportLogic::exportCsv() にて、ヘッダとデータの出力数が
				// 違っていたので、データ部にもダミーデータを追加。
				// 今までは、Excelで編集前はデータ部は1個少なく、編集後はヘッダ
				// とデータ部の個数が同じになっていた。
				$dummy = array_pop($data);	// ヘッダにテーブル名を定義したので、1つ余計なデータがある。
			}
			$csvRec = array_combine($colNames, $data);

			if($csvRec === false){
				$error = TRUE;
				$result['preview'] .= $line_count . ':ヘッダのカラム数とデータ数に相違があります。<br />';
				continue;
			}

			if(strlen($csvRec['id'])>0){
				$rec = $db->selectRecord($csvRec['id']);	// 更新データの場合
				if($rec == null){
					$result['preview'] .= $line_count.':DBに、ID='.$csvRec['id'].'のデータが存在しない為、更新できませんでした。<br />';
					continue;
				}
			}else{
				$rec = $db->getNewRecord();			// 新規データの場合
			}

			$owner = $db->getData($rec, 'owner');
			if(strlen($owner)>0 && strlen($csvRec['owner'])>0 ){
				if( $loginUserType == 'cUser' && $owner != $LOGIN_ID ){
					$result['preview'] .= $line_count.':他店舗用データ取込不可<br />';
					continue;
				}
				else if( $csvRec['owner'] != $owner ){
					$result['preview'] .= $line_count.':オーナー変更不可<br />';
					continue;
				}
			}


			foreach($db->colName as $colName){
				if(in_array($colName, $system_reserved)){
					continue; // システム予約につきスキップ
				}

				$value = $csvRec[$colName];
				if (isset($converter[$colName])) { // 変換処理
					$cvt = $converter[$colName];
						
					$value = call_user_func($cvt['program'], $cvt['table'], $value, $cvt['column'], $rec, $colName);
					if ($value === NULL) {
						$error = TRUE;
						$result['preview'] .= $line_count . ':「' . $csvRec[$colName] . '」は <a href="index.php?app_controller=search&type=' . $cvt['table'] . '&run=true">' . $cvt['table'] . '</a> テーブルに該当するデータが無い為、IDに変換できません。<br />';
						continue 2;
					}
				}
				$db->setData($rec, $colName, $value);
			}


			if(strlen($csvRec['id'])>0){
				self::dataProc( $db, $rec, true, $type );
				$db->updateRecord($rec);	// 更新データの場合
				$update_count++;
			}else{
				$buf=1;
				self::dataProc( $db, $rec, false, $type );
				$db->addRecord($rec);		// 新規データの場合
				$new_count++;
			}
		}

		fclose($fp);
		if (preg_match('/file\/upload/', $csvFile)) {
			unlink($csvFile);
		}

		if ($error) {
			if ($line_count > 1) {
				$result['preview'] .= ($line_count - 1) . '行目まで取り込みました。<br />';
			}
			$result['preview'] .= $line_count . '行目でエラーが発生しました。';
			if (!empty($data)) {
				$result['preview'] .= '<br />' . implode(',', $data);
			}
		} else {
			$buf =<<< EOD
{$line_count}行取り込みました。<br />
新規：{$new_count} 更新：{$update_count}<br />
<input type="button" value="ページ更新" onclick="location.reload();">
EOD;
			$result['preview'] .= $buf;
		}
		$result['token'] = SystemUtil::getAuthenticityToken();
		print json_encode($result);
		return;
	}


	/*
	 * 生成データをセットする
	 * 
	 * @param $db
	 * @param $rec
	 * @param $update
	 * @param $type
	 */
	private static function dataProc( &$db, &$rec, $update, $type )
	{
		global $loginUserType;
		global $LOGIN_ID;

		if(!$update)
		{// 新規登録のみの処理
			$db->setData( $rec, 'id',	  SystemUtil::getNewId( $db, $_GET['type']) );
			$db->setData( $rec, 'regist', time() );

			switch($type)
			{
			case 'mid':
			case 'fresh':
				switch($loginUserType)
				{
				case 'cUser':
					$db->setData( $rec, 'owner', $LOGIN_ID );
					break;
				}
				break;
			}
		}
		else
		{// 更新のみの処理
		}

	}


	/*
	 * 登録・更新前にデータに問題が無いかチェック
	 * 
	 * @param $db
	 * @param $rec
	 * @param $preview
	 * @param $line_count
	 * @param $update
	 * @return 問題がない場合はtrue
	 */
	private static function errorCheck( &$db, &$rec, &$preview, $line_count, $update )
	{
		$check = true;


		if(!$update)
		{
		}

		return $check;
	}

	/**
	 * アップロードされた画像の反映
	 * @param $unzip_dir
	 * @param $value
	 * @param $fileinfo
	 * @return type
	 */
	private static function image($unzip_dir, $value, $fileinfo)
	{
		if(strlen($value)>0 && isset($unzip_dir) ){
			if(file_exists($unzip_dir.$value)){
				// 存在しないファイル名の取得
				do{
					$dest = $fileinfo['dirname'].'/'.time() . '_' . rand() . '.'. pathinfo($value, PATHINFO_EXTENSION);
				} while(file_exists($dest));

				// ファイルの移動。 file/tmp に配置すると、
				// registProc()、editProc()でimageディレクトリへ移動させてくれる				
				copy($unzip_dir.$value, $dest);
				$value = $dest;
			}
		}

		return $value;
	}

	/**
	 * 名称をIDに変換
	 * name に同名が存在するデータには使えない
	 * @param type $type
	 * @param type $value
	 * @param type $column
	 * @param type $_rec
	 * @param type $colName
	 * @return type
	 */
	private static function name2id($type, $value, $column, $_rec = NULL, $colName = NULL) {
		if (strlen($value) > 0) {
			// テキスト中に/が含まれるものは退避しておく
			// ID複数格納している場合があるので/自体を退避しない
			$search = [ "" ];
			$replace = [ "" ];
			
			$value = str_replace( $search, $replace, $value);
			$names = explode('/', $value);
			$ids = array();

			$db = GMList::getDB($type);

			foreach ($names as $name) {
				$name = str_replace('～', '〜', str_replace($replace, $search, $name));
				$table = $db->getTable();
				$table = $db->searchTable($table, 'name', '=', $name);
				$table = $db->sortTable($table, 'shadow_id', 'desc');
				if ($db->existsRow($table)) {
					$rec = $db->getRecord($table, 0);
					$ids[] = $db->getData($rec, $column);
				} else {
					return NULL;
				}
			}
			$value = implode('/', $ids);
		}
		return $value;
	}

	/**
	 * YYYY/MM/DD HH:MM:SS をunix timestamp に変換
	 * @param type $dummy1
	 * @param type $value
	 * @param type $dummy2
	 * @param type $_rec
	 * @param type $colName
	 * @return type
	 */
	private static function timestamp($dummy1, $value, $dummy2 = NULL, $_rec, $colName) {

		if (strlen($value) == 0) {
			$value = '';
		} else {
			$value = strtotime($value);
		}
		return $value;
	}



	/**
	 * URLが有効かチェック
	 * @global url 
	 * @return 
	 */
	private static function url_exists( $url ){
	  if( ! $url && ! is_string( $url ) ){ return false; }
	 
	  $headers = @get_headers( $url );
	  if( preg_match( '/[2][0-9][0-9]|[3][0-9][0-9]/', $headers[0] ) ){
		return true;
	  }else{
		return false;
	  }
	}


	/**
	 * ZIPファイルを解凍
	 * @global zipfile 
	 * @return 
	 */
	private static function extractZIP($zipfile){
		$zip = new ZipArchive();
		$zip->open($zipfile);

		$fileinfo = pathinfo($zipfile);
		do{
			$unzip_dir = $fileinfo['dirname'] . '/' .time() . '_' . rand();
		}while(file_exists($unzip_dir));
		
		mkdir($unzip_dir, 0777);
		chmod($unzip_dir,0777);
		$unzip_dir .= '/';

		// 展開時にファイルが存在する場合は削除
		for ($i = 0; $i < $zip->numFiles; $i++){
			if(file_exists($unzip_dir.$zip->getNameIndex($i))){
				@unlink($unzip_dir.$zip->getNameIndex($i));
			}
		}

		if ($zip->extractTo($unzip_dir) !== TRUE) {
			$zip->close();
			return FALSE;
		}
		$files = array('unzip_dir'=>$unzip_dir);
		for ($i = 0; $i < $zip->numFiles; $i++) {
			$files[] = $zip->getNameIndex($i);
			if( file_exists($unzip_dir.$zip->getNameIndex($i))){
				chmod($unzip_dir.$zip->getNameIndex($i), 0666);
			}
		}
		$zip->close();
		@unlink($zipfile);
		return $files;
	}

	/**
	 * 
	 * @param path
	 */
	private static function rm_rf($path) {

		if(substr($path,-1)=='/'){
			$pat = $path.'*';
		}else{
			$pat = $path.'/*';
		}

		foreach (glob($pat) as $e) {
			if (is_dir($e)) {
				self::rm_rf($e);
			} else {
				unlink($e);
			}
		}
		rmdir($path);
	}


	private static function convert($tableName, $data, $label){
		$ids = explode('/', $data);

		$names = [];
		foreach( $ids as $id )
		{ $names[] = self::convertList( $label, $id ); }

		$data = implode('/',$names);
		return $data;
	}

	static $table = [];
	private static function convertList( $type, $id )
	{
		if( isset( self::$table[$type][$id] ) ) { return self::$table[$type][$id]; }

		switch($type)
		{
		default:
			$db = GMList::getDB($type);
			$table = $db->getTable();
			$table = $db->searchTable( $table, 'name', $id );
			$rec = $db->getFirstRecord($table);

			self::$table[$type][$id] = "";
			if( $rec ) { self::$table[$type][$id] = $db->getData($rec, 'id'); }
			break;
		}
		return self::$table[$type][$id];
	}

}
