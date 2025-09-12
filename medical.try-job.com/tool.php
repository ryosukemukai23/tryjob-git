<?php

	// ★★★デバッグ用出力を追加★★★
	error_log("🚩 Before loading meta.inc, LST[point]: " . (isset($LST['point']) ? $LST['point'] : 'Not defined yet'));

	foreach (glob(__DIR__.'/src/module/*/meta.inc') as $entry) {
		error_log("🚩 Loading: $entry");
		include_once $entry;
	}

	error_log("🚩 After loading meta.inc, LST[point]: " . (isset($LST['point']) ? $LST['point'] : 'Not defined yet'));

	include_once 'include/adminTool/conf.php';

	error_log("🚩 After loading conf.php, LST[point]: " . (isset($LST['point']) ? $LST['point'] : 'Not defined yet'));

	// tool.php 冒頭に追加
	foreach (glob(__DIR__ . '/src/module/*/meta.inc') as $entry) {
		include_once $entry;
	}

	include_once 'include/adminTool/conf.php';

	MVC::SetMVCPath( 'include/adminTool/mvc/' );
	MVC::SetExMVCPath( 'custom/tool/mvc/' );

	if( isset( $SYSTEM_INSTALL_STATUS[ 'disableTool' ] ) && $SYSTEM_INSTALL_STATUS[ 'disableTool' ] ) //tool.phpが無効になっている場合
	{
		header( 'Location:index.php' );
		exit();
	}

	$controllerName = ( array_key_exists( 'app_controller' , $_REQUEST ) ? $_REQUEST[ 'app_controller' ] : 'Index' );
	$type           = null;

	ob_start();

	foreach( MVC::GetNeedIncludes( $controllerName ) as $path ) //全てのインクルードパスを処理
		{ include_once $path; }

	$controller = MVC::Call( $controllerName , $type );

	ob_end_flush();
