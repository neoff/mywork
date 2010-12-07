<?php

	//phpinfo();
	//print_r($_SERVER);
	date_default_timezone_set( 'Europe/Moscow' );
	define("FILE", "config.ini");
class config{
		public function __set($name, $val)
		{
			$this->$name = $val;
		}
		public function __get($name)
		{
			$a = get_object_vars  ( $this  );
			if(!array_key_exists  ( $name , $a  ))
				$this->$name = "";
			return $this->$name;
			
			
		}
		public function __construct($file)
		{
			if(file_exists($file))
			{
				$conn = parse_ini_file(FILE);
				foreach ($conn as $key => $val) {
					$this->$key = $val;
				}
			}
		}
}
	$conn = new config(FILE);
	define( "DEBUG", ($conn->debug)?$conn->debug:True );
	define( 'CONNECTION', ($conn->base)?$conn->base:'develop' );
	define( "ROOT_PATH", dirname(__FILE__) );

	if(DEBUG)
	{
		ini_set('display_errors', True);
		error_reporting(E_ALL);
	}
	
	require_once ROOT_PATH . '/lib/Utils.php';
	require_once ROOT_PATH . '/lib/Exception.php';
	require_once ROOT_PATH . '/lib/ActiveRecord.php';
	require_once ROOT_PATH . '/lib/Routing.php';
	require_once ROOT_PATH . '/lib/Template.php';
	
	require_once ROOT_PATH . '/config/config.php';
	//require_once ROOT_PATH . '/config/template.php';
	require_once ROOT_PATH . '/config/routing.php'; # require after templates
	
	require_once ROOT_PATH . '/model/__init__.php';;
	
	$routing = new Routing\Routing();
