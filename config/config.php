<?php
/**
 * Файл конфигурации fast_cgi 
 * необходимо добавить коонекты к другим БД
 * (опционально)
 *
 * @since      07.10.2010 10:12:00
 * @author     enesterov
 * @category   none
 */

	/**
	 * поднимаем AR
	 */
	ActiveRecord\Config::initialize(function($cfg)
	{
		$cfg->set_model_directory('.');
		$cfg->set_connections(array(
			'test' => 'mysql://root:123456@localhost/test', 
			'develop' => 'mysql://baseadmin:C0ffeAmerikan0@192.168.1.226:33306/mvideo',
			'deploy' => 'mysql://baseadmin:C0ffeAmerikan0@diablopeerslave:33306/mvideo',
			'develop_test' => 'mysql://baseadmin:C0ffeAmerikan0@localhost:33306/mvideo',
		));
	});
	
	/**
	 * поднимаем урл
	 */
	Routing\Config::initialize(function($cfg)
	{
		$cfg->set_controller_directory(ROOT_PATH . '/controllers');
		
		$prefix = "mobile";
		
		if(get_key('region_id', 0) != 0) 
		{
			$_SERVER['REQUEST_URI'] = "/$prefix/shop/";
			
			if(get_key('category_id', -1) >= 0 || get_key('action', -1) >=0 || get_key('search')) 
				$_SERVER['REQUEST_URI'] = "/$prefix/category/";
			if(get_key('product_id')) 
				$_SERVER['REQUEST_URI'] = "/$prefix/product/";
		}
		
		
		$cfg->Map("^\/$prefix\/(\?region_id=0)?$", $controler="Region", $action="index", $_GET);
		$cfg->Map("^\/$prefix\/shop\/?$", $controler="Shops", $action="index", $_GET);
		$cfg->Map("^\/$prefix\/category\/?$", $controler="Category", $action="index", $_GET);
		$cfg->Map("^\/$prefix\/product\/?$", $controler="Product", $action="index", $_GET);
	});