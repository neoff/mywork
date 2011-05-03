<?php
/**  
 * библиотека отвечающая за роутинг
 * 
 * @package    lib
 * @subpackage routing
 * @since      07.10.2010 10:12:00
 * @author     enesterov
 * @category   none
 */

namespace Routing;
use ActiveRecord;
use Controllers;
use Closure;


class Config extends ActiveRecord\Singleton
{
	
	public static $route = array();
	public $key;
	private $controller_directory;
	private $mapper;
	public $prefix = "";

	public static function initialize(Closure $initializer)
	{
		$initializer(parent::instance());
	}
	
	public function MapSet($addr)
	{
		
	}
	public function Map($addr, $controler, $action, $argv=array())
	{
		if (!$addr || !$controler || !$action)
			throw new ActiveRecord\ConfigException("Build routing map faild");
			
		$file = ROOT_PATH . "/controllers/$controler.php";
		if (file_exists($file))
		{
			require_once $file;
		}
		$addr = "^\/?$this->prefix\/$addr\/?$";
		self::$route[$addr] =  array('Controllers\Controller' . $controler, $action, $argv);
	}
	
	public function set_controller_directory($dir)
	{
		if (!file_exists($dir))
			throw new ActiveRecord\ConfigException("Invalid or non-existent directory: $dir");

		$this->controller_directory = $dir;
	}
	
	public function set_prefix($prefix)
	{
		$this->prefix = $prefix;
	}

	/**
	 * Returns the controller directory.
	 *
	 * @return string
	 */
	public function get_controller_directory()
	{
		return $this->controller_directory;
	}
	private function Set(){}
	private static function init(){}
};
?>