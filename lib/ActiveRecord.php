<?php
if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50300)
	die('PHP ActiveRecord requires PHP 5.3 or higher');

define('PHP_ACTIVERECORD_VERSION_ID','1.0');

require 'ar/Singleton.php';
require 'ar/Config.php';
require 'ar/Utils.php';
require 'ar/DateTime.php';
require 'ar/Model.php';
require 'ar/Table.php';
require 'ar/ConnectionManager.php';
require 'ar/Connection.php';
require 'ar/SQLBuilder.php';
require 'ar/Reflections.php';
require 'ar/Inflector.php';
require 'ar/CallBack.php';
require 'ar/Exceptions.php';

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
		require $file;
}
?>
