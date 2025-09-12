<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of csvExportLogic
 *
 * @author Yuji Noizumi <noizumi@websquare.co.jp>
 */
class csvExportLogic {

	static function exportCsv($table) {
		global $ACTIVE_NONE;
		global $SYSTEM_CHARACODE;
		global $loginUserType;

		if($loginUserType != 'admin' && $loginUserType != 'cUser'){
			return;
		}

		ob_end_clean();

		$tableName = $_GET['type'];
		// データレコード
		$db = GMList::getDB($tableName);

		if (isset($_SERVER['PATH_INFO'])) {
			$outfile = str_replace('/', '', $_SERVER['PATH_INFO']);
		} else {

			$outfile = $db->tablePlaneName . date('YmdHis') . '.csv';
		}

		header("Content-Type: application/octet-stream");
		header('Content-Disposition: attachment;filename="' . $outfile . '"');
		header("Content-Transfer-Encoding: binary");
		header('Cache-Control: max-age=0');

		$handle = fopen('php://output', 'w');

		// ヘッダ出力
		$colName = $db->colName;
		array_push($colName, $db->tablePlaneName);
		fputcsv($handle, $colName);

		$converter = self::getConverter($tableName);

		$row = $db->getRow($table);
		$total = 0;
		$count = $row;
		for ($i = 0; $i < $row; $i++) {
			// mid
			$rec = $db->getRecord($table, $i);

			$out = array_fill_keys($db->colName, '');
			foreach ($db->colName as $key) {
				$out[$key] = $db->getData($rec, $key);
				if (isset($converter[$key])) {
					$cvt = $converter[$key];
					$out[$key] = call_user_func($cvt['program'], $cvt['table'], $out[$key], $cvt['column']);
				}
			}

			$buf = mb_convert_variables('sjis-win', $SYSTEM_CHARACODE, $out);
			// ヘッダ行の末尾にテーブル名を付けているので、個数を合わせる
			array_push($out, '');
			fputcsv($handle, $out);
		}

		fclose($handle);
		exit(0);
	}

	private static function id2name($tableName, $data, $column) {
		$ids = explode('/', $data);

		$db = GMList::getDB($tableName);
		$table = $db->getTable();
		$table = $db->searchTable( $table, 'id', 'in', $ids );
		$names = $db->getDataList( $table, $column );

		if( $names == null ) { $names = []; }
		$data = implode('/',$names);
		return $data;
	}

	private static function timestamp($format, $data, $dummy = NULL) {
		if (empty($data)) {
			$data = '';
		} else {
			$data = date($format, $data);
		}
		return $data;
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
			self::$table[$type][$id] = SystemUtil::getTableData( $type, $id, "name" );
		break;
		}
		return self::$table[$type][$id];
	}


	private static function getConverter($tableName) {

		switch ($tableName) {
			case 'mid':
			case 'fresh':
				// 変換処理用 (DBテーブル名又は日付フォーマット、処理プログラム、データ取得カラム名)
				$converter = array(
					'category'=>			array('table'=>'items_type','program'=>'self::id2name','column'=>'name'),
					'work_style'=>			array('table'=>'items_form','program'=>'self::id2name','column'=>'name'),
					'work_place_adds'=>		array('table'=>'adds',		'program'=>'self::id2name','column'=>'name'),
					'work_place_add_sub'=>	array('table'=>'add_sub',	'program'=>'self::id2name','column'=>'name'),
					'traffic1_adds'=>		array('table'=>'adds',		'program'=>'self::id2name','column'=>'name'),
					'traffic1_line'=>		array('table'=>'line',		'program'=>'self::id2name','column'=>'name'),
					'traffic1_station'=>	array('table'=>'station',	'program'=>'self::id2name','column'=>'name'),
					'traffic2_adds'=>		array('table'=>'adds',		'program'=>'self::id2name','column'=>'name'),
					'traffic2_line'=>		array('table'=>'line',		'program'=>'self::id2name','column'=>'name'),
					'traffic2_station'=>	array('table'=>'station',	'program'=>'self::id2name','column'=>'name'),
					'traffic3_adds'=>		array('table'=>'adds',		'program'=>'self::id2name','column'=>'name'),
					'traffic3_line'=>		array('table'=>'line',		'program'=>'self::id2name','column'=>'name'),
					'traffic3_station'=>	array('table'=>'station',	'program'=>'self::id2name','column'=>'name'),
					'traffic4_adds'=>		array('table'=>'adds',		'program'=>'self::id2name','column'=>'name'),
					'traffic4_line'=>		array('table'=>'line',		'program'=>'self::id2name','column'=>'name'),
					'traffic4_station'=>	array('table'=>'station',	'program'=>'self::id2name','column'=>'name'),
					'traffic5_adds'=>		array('table'=>'adds',		'program'=>'self::id2name','column'=>'name'),
					'traffic5_line'=>		array('table'=>'line',		'program'=>'self::id2name','column'=>'name'),
					'traffic5_station'=>	array('table'=>'station',	'program'=>'self::id2name','column'=>'name'),
					'addition'=>			array('table'=>'job_addition','program'=>'self::id2name','column'=>'name'),
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

}
