<?php
/**
 * Файл конфигурации url 
 *
 * @since      07.10.2010 10:12:00
 * @author     enesterov
 * @category   none
 */

	

	Routing\Config::initialize(function($cfg)
	{
		/* ****************************  */
		$prefix = "/mobile";
		$region_id = (!array_key_exists('region_id', $_GET))?0:$_GET['region_id'];
		$shop_id = (!array_key_exists('shop_id', $_GET))?0:$_GET['shop_id'];
		$category_id = (!array_key_exists('category_id', $_GET))?-1:$_GET['category_id'];
		$search_name = false;
		$product_name = false;
		
		if($region_id!=0) 
		{
			$_SERVER['REQUEST_URI'] = "/shop/";
			
			if($category_id >= 0) 
				$_SERVER['REQUEST_URI'] = "/category/";
		}
		
		//print $category_id.$_SERVER['REQUEST_URI'];
		$cfg->set_controller_directory( ROOT_PATH . '/controllers');
		$cfg->MapSet("$prefix");
		$cfg->Map("^\/$", $controler="Region", $action="index", $region_id);
		$cfg->Map("^\/shop\/?$", $controler="Shops", $action="index", $region_id);
		$cfg->Map("^\/category\/?$", $controler="Category", $action="index", array($region_id, $category_id));
	});