<?php
if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50300)
	die('PHP ActiveRecord requires PHP 5.3 or higher');

define('PHP_ACTIVERECORD_VERSION_ID','1.0');

require_once 'ar/Singleton.php';
require_once 'ar/Config.php';
require_once 'ar/Utils.php';
require_once 'ar/DateTime.php';
require_once 'ar/Model.php';
require_once 'ar/Table.php';
require_once 'ar/ConnectionManager.php';
require_once 'ar/Connection.php';
require_once 'ar/SQLBuilder.php';
require_once 'ar/Reflections.php';
require_once 'ar/Inflector.php';
require_once 'ar/CallBack.php';
require_once 'ar/Exceptions.php';

spl_autoload_register('activerecord_autoload');

function activerecord_autoload($class_name)
{
	$path = ActiveRecord\Config::instance()->get_model_directory();
	$root = realpath(isset($path) ? $path : '.');

	if (($namespaces = ActiveRecord\get_namespaces($class_name)))
	{
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
