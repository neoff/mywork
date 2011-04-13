<?php
/**  
 * библиотека отвечающая за роутинг
 * 
 * @package    lib
 * @since      08.10.2010 14:00:27
 * @author     enesterov
 */

if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50300)
	die('PHP Routing requires PHP 5.3 or higher');

	require_once 'ActiveRecord/Singleton.php';
	require_once 'routing/Config.php';
	require_once 'routing/Connection.php';


spl_autoload_register('routing_autoload');

function routing_autoload($class_name)
{
	
	$path = Routing\Config::instance()->get_controller_directory();
	$root = realpath(isset($path) ? $path : '.');

	if (($namespaces = ActiveRecord\get_namespaces($class_name)))
	{
		//print_r($namespaces);
		$class_name = array_pop($namespaces);
		$directories = array();

		foreach ($namespaces as $directory)
			$directories[] = $directory;

		$root .= DIRECTORY_SEPARATOR . implode($directories, DIRECTORY_SEPARATOR);
	}

	$file = "$root/$class_name.php";

	if (file_exists($file))
		require_once $file;
}
