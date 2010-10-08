<?php
	namespace Controllers;
	
	$region_id = (array_key_exists("region_id", $_REQUEST))?$_REQUEST["region_id"]:0;

	
	
class Routing{
	public $route;
	public function Routing()
	{
		print $this->route;
	}
	public function Map($array)
	{
		call_user_func(__NAMESPACE__ .'\ControllerRegion::index');
	}
	public function Set( $addr, $controler, $action )
	{
		$this->route = $array;
	}
}

//map.connect("/order/free/", controller="order", action="free_place")
Routing::Set( "/", "ControllerRegion", "index" );
