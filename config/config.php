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

	/*
	 * поднимаем AR
	 */
	ActiveRecord\Config::initialize(function($cfg)
	{
		$cfg->set_model_directory(ROOT_PATH . '/model');
		$cfg->set_connections(array(
			'test' => 'mysql://root:123456@localhost/test',
			'develop' => 'mysql://baseadmin:C0ffeAmerikan0@diablopeerslave:33306/mvideo',
			'deploy' => 'mysql://baseadmin:C0ffeAmerikan0@diablopeerslave:33306/mvideo',
			'develop_test' => 'mysql://baseadmin:C0ffeAmerikan0@localhost:33306/mvideo',
		));
	});

	/*
	 * поднимаем урл
	 */
	Routing\Config::initialize(function($cfg)
	{
		$cfg->set_controller_directory(ROOT_PATH . '/controllers');
		$cfg->set_prefix("mobile");
		$region = get_key('region_id', -1);

		if($region > 0)
		{
			$_SERVER['REQUEST_URI'] = makeUrl(array($cfg->prefix,"shop"));

			if(get_key('category_id', -1) >= 0 || get_key('action', -1) >=0 || get_key('search'))
				$_SERVER['REQUEST_URI'] = makeUrl(array($cfg->prefix,"category"));
			if(get_key('product_id'))
				$_SERVER['REQUEST_URI'] = makeUrl(array($cfg->prefix,"product"));
			if(get_key('actions'))
				$_SERVER['REQUEST_URI'] = makeUrl(array($cfg->prefix,"actions"));
			if(get_key('pickup_product_id'))
				$_SERVER['REQUEST_URI'] = makeUrl(array($cfg->prefix,"pickup"));
			if(get_key('shops_product_id'))
				$_SERVER['REQUEST_URI'] = makeUrl(array($cfg->prefix,"shops"));
			if(get_key('cart'))
				$_SERVER['REQUEST_URI'] = makeUrl(array($cfg->prefix,"cart"));

		}
		else
		{
			//if(get_key('start') == 1)
			$_SERVER['REQUEST_URI'] = makeUrl(array($cfg->prefix,"start"));

			if(get_key('polls', -1) >= 0 )
				$_SERVER['REQUEST_URI'] = makeUrl(array($cfg->prefix,"polls"));

			if(get_key('links', -1) >= 0 )
				$_SERVER['REQUEST_URI'] = makeUrl(array($cfg->prefix,"links"));

			if($region === '0')
				$_SERVER['REQUEST_URI'] = makeUrl(array($cfg->prefix,"region"));
		}

		$cfg->Map("region", $controler="Region", $action="index");
		$cfg->Map("shop", $controler="Shops", $action="index");
		$cfg->Map("shops", $controler="Shops", $action="shops");
		$cfg->Map("category", $controler="Category", $action="index", $_GET);
		$cfg->Map("product", $controler="Product", $action="index", $_GET);
		$cfg->Map("pickup", $controler="Pickup", $action="index", $_GET);
		$cfg->Map("start", $controler="Start", $action="index", $_GET);
		$cfg->Map("actions", $controler="Actions", $action="index", $_GET);
		$cfg->Map("polls", $controler="Polls", $action="index", $_GET);
		$cfg->Map("links", $controler="Links", $action="index");
		$cfg->Map("cart", $controler="Cart", $action="index", $_REQUEST);
	});