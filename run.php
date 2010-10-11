<?php

	
	date_default_timezone_set( 'Europe/Moscow' );
	define( "DEBUG", True );
	define( 'CONNECTION', 'develop' );
	define( "ROOT_PATH", dirname(__FILE__) );

	if(DEBUG)
	{
		ini_set('display_errors', 1);
		error_reporting(E_ALL);
	}
	require_once ROOT_PATH . '/lib/ActiveRecord.php';
	require_once ROOT_PATH . '/lib/Routing.php';
	
	require_once ROOT_PATH . '/config/config.php';
	require_once ROOT_PATH . '/config/template.php';
	require_once ROOT_PATH . '/config/routing.php'; # require after templates
	
	require_once ROOT_PATH . '/model/__init__.php';;
	
	$routing = new Routing\Routing();
