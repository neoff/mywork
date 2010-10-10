<?php

namespace Routing;
use ActiveRecord;

class Routing extends Config{
	
	public function __construct()
	{
		$this->Set();
		$this->init();
	}
	private function Set()
	{
		preg_match("/\/(?P<controller>\w+)\/(?P<action>\w+)\/?/i", $_SERVER['REQUEST_URI'], $matches);
		
		if(array_key_exists("controller", $matches) && array_key_exists("action", $matches)) {
			$this->key = "/%s/%s/" % array($matches["controller"], $matches["action"]);
			Routing::Map(  $this->key , $matches["controller"], $matches["action"] );
		} else
		{
			//throw new ActiveRecord\ConfigException("no exist controller or action");
		}
	}
	private static function yeld($item2, $key)
	{
		preg_match("@" . $key . "@i", $_SERVER['REQUEST_URI'], $matches);
		print_r($matches);
		if ($matches)
			$this->m = $matches[0];
			
		return $this->m;
	}
	private static function init() 
	{
		//print_r(self::$route);
		$item = false;
		$arrKey;
		while (list($key, $value) = each(self::$route)) {
			preg_match("@" . $key . "@i", $_SERVER['REQUEST_URI'], $matches);
			if ($matches)
			{
				$item = True;
				$arrKey = $key;
				break;
			}
		}
		if(array_key_exists($arrKey, self::$route)) {
    		$ins = new self::$route[$arrKey][0];
    		$fnc = self::$route[$arrKey][1];
    		$argv = self::$route[$arrKey][2];
    		$ins->$fnc($argv);
		} else {
			throw new ActiveRecord\ConfigException("Invalid or non-existent directory: $dir");
		}
	}
}