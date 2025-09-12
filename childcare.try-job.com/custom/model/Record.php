<?php

	class Record
	{

		
		/**
		 * sort_rankがあるテーブルのレコードを引数のデータを元に作成する
		 *
		 * @param tableName テーブル名
		 * @param data レコード内容
		 * @return レコード
		 */
		function regist( $tableName, $data )
		{
			$db	 = GMList::getDB($tableName);

			$rec = $db->getNewRecord( $data );
			
			$colList = array('apply', 'employment', 'gift', 'term', 'cost' );
			foreach( $colList as $col )
			{
				$val = $db->getData($rec, $col);

				if( strlen($val) )
				{
					$val = abs((int)mb_convert_kana($val, 'n'));
					$db->setData( $rec, $col, $val );
				}
			}
			$db->setData($rec, 'id',		 SystemUtil::getNewId( $db, $tableName ) );
			$db->setData($rec, 'delete_flg', TRUE );
			$db->setData($rec, 'sort_rank',	 time() );
			$db->setData($rec, 'regist',	 time() );
			
			$db->addRecord( $rec );

			return $rec;

		}

		/**
		 * sort_rankがあるテーブルの指定レコードの内容を変更する
		 *
		 * @param tableName テーブル名
		 * @param id レコードID
		 * @param data レコード内容
		 * @return レコード
		 */
		function edit( $tableName, $id, $data )
		{
			$db	 = GMList::getDB($tableName);

			$rec = $db->selectRecord($id);

			$colList = array( 'name','url','inquiry','faqurl','base_charge', 'apply', 'employment', 'gift', 'term', 'cost','disp','area_id','adds_id' );
			foreach( $colList as $col )
			{
				if( strlen($data[$col]) )
				{
					$val = $data[$col];
					if( $col != 'name' && $col != 'url' && $col != 'faqurl' && $col != 'inquiry' && $col != 'base_charge' && $col != 'disp' && $col != 'area_id' && $col != 'adds_id') { $val = abs((int)mb_convert_kana($data[$col], 'n')); }
					$db->setData( $rec, $col, $val );
				}
			}

			$db->updateRecord($rec);

			return $rec;
		}


		/**
		 * 指定テーブルの指定レコードを削除する
		 *
		 * @param tableName テーブル名
		 * @param id レコードID
		 */
		function delete( $tableName, $id )
		{
			$db	 = GMList::getDB($tableName);
			$rec = $db->selectRecord($id);
			// 削除可能レコードのみ削除を実行
			if( $db->getData( $rec, 'delete_flg' ) ) { $db->deleteRecord($rec); }

		}

	}

?>