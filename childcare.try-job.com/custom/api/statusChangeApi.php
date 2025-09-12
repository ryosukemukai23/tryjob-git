<?php

//管理者用各検索ページの一括変更処理
class mod_statusChangeApi extends apiClass {

	//bill 決済フラグ変更
	function changePayment($param) {
		global $loginUserType;
		if ($loginUserType != 'admin')
			return;
		$status = array('payOK' => true, 'payNG' => false);

		$json['result'] = 'success';
		$json['count'] = 0;
		$json['message'] = '';

		$type = $param['type'];
		$id = explode('/', $param['id']);

		$db = GMList::getDB($type);
		$table = $db->getTable();
		$table = $db->searchTable($table, 'id', 'in', $id);

		//案件が存在しない、または入金通知:未かつ入金確認:済への変更を弾く為の判定
		$table = $db->searchTable($table, 'notice', '=', true);

		$table = $db->searchTable($table, 'pay_flg', '!', $status[$param['val']]);

		$json['count'] = $db->getRow($table);

		// 変更前に変更するIDのリスト取得
		$idList = $db->getDataList($table, 'id');
		foreach ($idList as $key => $val) {
			$rec = $db->selectRecord($val);
			if ($status[$param['val']] == true) {
				MailLogic::NoticeAcceptPayment($rec);
			} else {
				MailLogic::NoticeCancelPayment($rec);
			}
		}

		if ($status[$param['val']] == true) {
			$db->setTableDataUpdate($table, 'pay_time', time());
		} else {
			$db->setTableDataUpdate($table, 'pay_time', 0);
		}
		$db->setTableDataUpdate($table, 'pay_flg', $status[$param['val']]);

		print json_encode($json);
	}

	//bill 通知変更
	function changePaymentNotice($param) {
		global $loginUserType;
		if ($loginUserType != 'admin')
			return;
		$status = array('payNoticeOK' => true, 'payNoticeNG' => false);

		$json['result'] = 'success';
		$json['count'] = 0;
		$json['message'] = '';

		$type = $param['type'];
		$id = explode('/', $param['id']);

		$db = GMList::getDB($type);
		$table = $db->getTable();
		$table = $db->searchTable($table, 'id', 'in', $id);

		if (!$status[$param['val']]) {
			//入金通知:未かつ入金確認:済への変更を弾く為の判定
			$table = $db->searchTable($table, 'notice', '=', true);
			$table = $db->searchTable($table, 'pay_flg', '!', true);
		} else {
			$table = $db->searchTable($table, 'notice', '!', $status[$param['val']]);
		}

		$json['count'] = $db->getRow($table);
		$db->setTableDataUpdate($table, 'notice', $status[$param['val']]);

		print json_encode($json);
	}

	//inquiry 対応フラグ変更
	function changeSupported($param) {
		global $loginUserType;
		if ($loginUserType != 'admin')
			return;
		$status = array('supportedOK' => true, 'supportedNG' => false);

		$json['result'] = 'success';
		$json['count'] = 0;
		$json['message'] = '';

		$type = $param['type'];
		$id = explode('/', $param['id']);

		$db = GMList::getDB($type);
		$table = $db->getTable();
		$table = $db->searchTable($table, 'id', 'in', $id);

		$table = $db->searchTable($table, 'supported', '!', $status[$param['val']]);

		$json['count'] = $db->getRow($table);
		$db->setTableDataUpdate($table, 'supported', $status[$param['val']]);

		print json_encode($json);
	}

	function changeFaled(&$json, $result, $message) {
		$json['result'] = $result;
		$json['message'] = $message;
	}

	//entry 進捗変更
	function changeProgress(&$param) {
		global $loginUserType;
		global $LOGIN_ID;

		if ($loginUserType != 'admin' && $loginUserType != 'aUser' && $loginUserType != 'cUser') {
			return;
		}

		$json['result'] = 'success';
		$json['count'] = 0;
		$json['message'] = '';

		$type = $param['type'];
		$id = explode('/', $param['id']);

		$db = GMList::getDB($type);
		$table = $db->getTable();
		$table = $db->searchTable($table, 'id', 'in', $id);

		if ($loginUserType == 'cUser') {
			$table = $db->searchTable($table, 'items_owner', '=', $LOGIN_ID);
		}
		$table = $db->searchTable($table, 'status', '!', 'SUCCESS');
		$table = $db->searchTable($table, 'status', '!', 'FAILE');
		$table = $db->searchTable($table, 'status', '!', $param['val']);

		$json['count'] = $db->getRow($table);

		switch ($param['val']) {
			case 'START':
			case 'FAILE':
			case 'EP001':
			case 'EP002':
			case 'SUCCESS':
				// 変更前に変更するIDのリスト取得
				$idList = $db->getDataList($table, 'id');
				// データ変更
				$db->setTableDataUpdate($table, 'status', $param['val']);

				if( count($idList) > 0 )
				{
					foreach ($idList as $key => $val) {
						$rec = $db->selectRecord($val);
						MailLogic::sendEntryStatusChenge($rec);
						if ($param['val'] == 'SUCCESS') {
							pay_jobLogic::addEmploymentLog($rec); //採用課金
						}
					}
				}
				break;
		}
		print json_encode($json);
	}

	//common アクティベート変更
	function changeActivate($param) {
		global $ACTIVE_NONE;
		global $ACTIVE_ACCEPT;
		global $ACTIVE_DENY;
		global $loginUserType;
		$status = array('Unconfirmed' => $ACTIVE_NONE, 'allowed' => $ACTIVE_ACCEPT, 'notallowed' => $ACTIVE_DENY);
		if (empty($status[$param['val']]))
			return;
		if ($loginUserType != 'admin' && $loginUserType != 'aUser') {
			return;
		}

		$json['result'] = 'success';
		$json['count'] = 0;
		$json['message'] = '';

		$type = $param['type'];
		$id = explode('/', $param['id']);

		$db = GMList::getDB($type);
		$table = $db->getTable();
		$table = $db->searchTable($table, 'id', 'in', $id);

		$table = $db->searchTable($table, 'activate', '!', $status[$param['val']]);

		$idList = $db->getDataList($table, 'id');

		switch ($param['type']) {
			case 'cUser':
			case 'nUser':
				if ($status[$param['val']] == $ACTIVE_ACCEPT) {
					foreach ($idList as $key => $val) {
						$rec = $db->selectRecord($val);
						MailLogic::userRegistComp($rec, $type, 'statusChange');
					}
				}
				break;
			case 'mid':
			case 'fresh':
				if($status[$param['val']] == $ACTIVE_ACCEPT){
					foreach($idList as $key=>$val){
						$rec = $db->selectRecord($val);
					 	MailLogic::noticeProjectActivate($type, $rec);
					}
				}
				$table = $db->searchTable($table, 'delete_flg', '!', true);
				break;
			case 'interview':
				if ($status[$param['val']] == $ACTIVE_ACCEPT) {
					foreach ($idList as $key => $val) {
						$rec = $db->selectRecord($val);
						MailLogic::noticeInterviewActivate($rec);
					}
				}
				break;
		}
		$json['count'] = $db->getRow($table);
		$db->setTableDataUpdate($table, 'activate', $status[$param['val']]);

		print json_encode($json);
	}

	//common データ削除
	function delete($param) {
		global $gm;
		global $loginUserType;
		if ($loginUserType != 'admin')
			return;

		$json['result'] = 'success';
		$json['count'] = 0;
		$json['message'] = '';

		$type = $param['type'];
		$id = explode('/', $param['id']);

		$db = GMList::getDB($type);
		$table = $db->getTable();
		$table = $db->searchTable($table, 'id', 'in', $id);

		switch ($type) {
			case 'nUser':
				foreach ($id as $key => $val) {
					resumeLogic::delete($val);
				}
				$json['count'] = $db->getRow($table);
				$db->deleteTable($table);
				break;
			case 'cUser':
				$mdb = $gm['mid']->getDB();
				$mtable = $mdb->getTable();
				$mtable = $mdb->searchTable($mtable, 'owner', 'in', $id);
				$mdb->setTableDataUpdate($mtable, 'delete_flg', true);
				$mdb->setTableDataUpdate($mtable, 'delete_date', time());

				$fdb = $gm['fresh']->getDB();
				$ftable = $fdb->getTable();
				$ftable = $fdb->searchTable($ftable, 'owner', 'in', $id);
				$fdb->setTableDataUpdate($ftable, 'delete_flg', true);
				$fdb->setTableDataUpdate($ftable, 'delete_date', time());

				$json['count'] = $db->getRow($table);
				$db->deleteTable($table);
				break;
			case 'mid':
			case 'fresh':
				$table = $db->searchTable($table, 'delete_flg', '!', true);

				$json['count'] = $db->getRow($table);
				$db->setTableDataUpdate($table, 'delete_flg', true);
				$db->setTableDataUpdate($table, 'delete_date', time());
				break;
			default:
				// これはちょっと怖いので封印
				/**
				  $json['count'] = $db->getRow($table);
				  $db->deleteTable($table);
				 */
				break;
		}

		print json_encode($json);
	}

	/**
	 * 公開・非公開切り替え
	 * @global string $loginUserType
	 * @param array $param
	 * @return json
	 */
	function changePublish($param) {
		global $loginUserType;

		$status = array('publishon' => 'on', 'publishoff' => 'off');
		if (empty($status[$param['val']])) {
			return;
		}
		if ($loginUserType != 'admin' && $loginUserType != 'aUser' && $loginUserType != 'cUser') {
			return;
		}

		$json['result'] = 'success';
		$json['count'] = 0;
		$json['message'] = '';

		$type = $param['type'];
		$id = explode('/', $param['id']);

		$db = GMList::getDB($type);
		$table = $db->getTable();
		$table = $db->searchTable($table, 'id', 'in', $id);

		switch ($type) {
			case 'mid':
			case 'fresh':
				$table = $db->searchTable($table, 'publish', '=', $status[$param['val']] == 'on' ? 'off' : 'on');
				$json['count'] = $db->getRow($table);

				$db->setTableDataUpdate($table, 'publish', $status[$param['val']]);
				break;
			default:
				break;
		}
		print json_encode($json);
	}

}
