<?php
	//phpinfo();
	//print_r($_SERVER);
	date_default_timezone_set( 'Europe/Moscow' );
	define( "ROOT_PATH", dirname(__FILE__) );
	define("FILE", ROOT_PATH . "/config.ini");
	require_once ROOT_PATH . '/conf_parse.php';
	
	$conn = new config(FILE);
	define( "DEBUG", ($conn->debug)? $conn->debug: True );
	define( 'CONNECTION', ($conn->base)? $conn->base: 'develop' );
	define( 'LIB_PATH', ($conn->lib)? $conn->lib: ROOT_PATH.'/lib' );
	define( 'MVIDEO_PATH', ($conn->mvideo)? $conn->mvideo: dirname(ROOT_PATH) . "/mvideo");
	
	//var_dump(MVIDEO_PATH);
	if(DEBUG)
	{
		ini_set('display_errors', True);
		error_reporting(E_ALL);
	}
	$except = false;
	require_once ROOT_PATH . '/lib/Exception.php';
	require_once ROOT_PATH . '/lib/Utils.php';
	require_once ROOT_PATH . '/config/config.php';
	$ar = new ActiveRecord();
	$routing = new Routing\Routing();
