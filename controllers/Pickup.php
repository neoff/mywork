<?php
/**
 * выбор магазина для пикапа
 * 
 * @package     Pickup
 * @subpackage  Controller
 * @since       20.06.2011 11:55:53
 * @author      enesterov
 * @category    controller
 *
 */

	namespace Controllers;
	use Models;
	use Template;

class ControllerPickup extends Template\Template{
	
	private $region_id;
	
	public function index($get)
	{
		$this->product_id = get_key('pickup_product_id');
		$this->region_id = get_key('region_id', 1);
		
		$this->pickup_shops="";
		$this->pickup_shops->addAttribute("region_id", $this->region_id);
		
		
		$shop = $this->pickup_shops->addChild("shop");
		//hard code
		$shop->addChild("shop_id", 11);
		$shop->addChild("shop_name", 'М.ВИДЕО 24 часа на Чонгарском бульваре');
		$node->addChild("address", 'Чонгарский б-р, 3, к.2');
		$metro = $shop->addChild("metro");
		$node->addChild("station", 'Варшавская');
		
		$shop->addChild("shop_id", 14);
		$shop->addChild("shop_name", 'Магазин на Измайловском валу');
		$node->addChild("address", 'Измайловский Вал, 3');
		$metro = $shop->addChild("metro");
		$node->addChild("station", 'Семеновская');
	}
}