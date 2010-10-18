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
		$prefix = "mobile";
		$url_prefix = "\/$prefix";
		$region_id = (!array_key_exists('region_id', $_GET))?0:$_GET['region_id'];
		$shop_id = (!array_key_exists('shop_id', $_GET))?0:$_GET['shop_id'];
		$product_id = (!array_key_exists('product_id', $_GET))?false:$_GET['product_id'];
		$act = (!array_key_exists('action', $_GET))?-1:$_GET['action'];
		$category_id = (!array_key_exists('category_id', $_GET))?-1:$_GET['category_id'];
		#IN CATEGORIES
		$ask = (!array_key_exists('ask', $_GET))?false:$_GET['ask'];
		$reviews = (!array_key_exists('reviews', $_GET))?false:$_GET['reviews'];
		$search = (!array_key_exists('search', $_GET))?false:$_GET['search'];
		
		$width = (!array_key_exists('width', $_GET))?false:$_GET['width'];
		$height = (!array_key_exists('height', $_GET))?false:$_GET['height'];
		if($region_id!=0) 
		{
			$_SERVER['REQUEST_URI'] = "/$prefix/shop/";
			
			if($category_id >= 0 || $act >=0 || $search) 
				$_SERVER['REQUEST_URI'] = "/$prefix/category/";
			if($product_id) 
				$_SERVER['REQUEST_URI'] = "/$prefix/product/";
		}
		
		$cfg->set_controller_directory( ROOT_PATH . '/controllers');
		//$cfg->MapSet("$prefix");
		$cfg->Map("^$url_prefix\/(\?region_id=0)?$", $controler="Region", $action="index", $region_id);
		
		#shop
		#$cfg->Map("^^$url_prefix\/\?region_id=[1-9]\d*$", $controler="Shops",$action="index", array($region_id, $width, $height));
		$cfg->Map("^$url_prefix\/shop\/?$", $controler="Shops", 
					$action="index", array($region_id, $width, $height));
		#category
		#$cfg->Map("^$url_prefix\/\?region_id=[1-9]\d*&category_id=\d+?$", $controler="Category", $action="index", array($region_id, $category_id));
		$cfg->Map("^$url_prefix\/category\/?$", $controler="Category", 
		$action="index", array($region_id, $category_id, $act, $search));
		#product
		#$cfg->Map("^$url_prefix\/\?region_id=[1-9]\d*&product_id=\d+$", $controler="Product", $action="index", array($region_id, $product_id));
		$cfg->Map("^$url_prefix\/product\/?$", $controler="Product", 
					$action="index", array($region_id, $product_id, $ask, $reviews));
		#action
		#$cfg->Map("^$url_prefix\/\?region_id=[1-9]\d*&product_id=\d+&action=\d+$", $controler="Product", $action="index", array($region_id, $product_id, $action));
		$cfg->Map("^$url_prefix\/action\/?$", $controler="Category", 
					$action="index", array($region_id, $category_id, $act, $search));
	});