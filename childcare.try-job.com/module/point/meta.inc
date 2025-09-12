<?php

    error_log('✅ [point] meta.inc is loaded!');

    $moduleName = 'point';

    $MODULES[ $moduleName ] = Array(
        'version' => '1.0',
        'summary' => '企業ポイント管理',
    );

    include_once './module/' . $moduleName . '/custom/logic/pointLogic.php';

    // ■ テーブル定義
    $EDIT_TYPE = 'point';
    $TABLE_NAME[] = $EDIT_TYPE;
    $THIS_TABLE_IS_USERDATA[$EDIT_TYPE] = false;
    $THIS_TABLE_REGIST_USER[$EDIT_TYPE] = Array('admin');
    $THIS_TABLE_EDIT_USER[$EDIT_TYPE] = Array('admin');
    $LST[$EDIT_TYPE] = '[point]/point.csv';
    $TDB[$EDIT_TYPE] = '[point]/point.csv';
    $ID_HEADER[$EDIT_TYPE] = 'PT';
    $ID_LENGTH[$EDIT_TYPE] = 10;

?>