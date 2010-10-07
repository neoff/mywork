<?php

	date_default_timezone_set( 'Europe/Moscow' );
	define( "DEBUG", True );
	define( 'CONNECTION', 'test' );
	define( "ROOT_PATH", dirname(__FILE__) );

	if(DEBUG)
	{
		ini_set('display_errors', 1);
		error_reporting(E_ALL);
	}


	require_once ROOT_PATH . '/config/config.php';
	require_once ROOT_PATH . '/controllers/routing.php';
	require_once ROOT_PATH . '/controllers/template.php';
