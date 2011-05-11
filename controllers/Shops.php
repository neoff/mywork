<?php
/**  
 * 
 * 
 * @package    controllers
 * @subpackage Shops
 * @since      08.10.2010 14:00:27
 * @author     enesterov
 * @category   controller
 */


	namespace Controllers;
	use Models;
	use Template;
	
class ControllerShops extends Template\Template{
	
	private $region_id;
	
	public function index()
	{
		$this->setVar();
		$this->region_id = get_key('region_id', 1);
		
		$this->mem_key = 'region_'.$this->region_id;
		
		
		if(!$this->mem_flush && $this->getMemObj())
			return true;
		
		$this->mem_time = 60*60*24*7;
		//var_dump($shop_id);
		//list($shop_id, $width, $height) = $shop_id;
		
		$options = array('region_id' => $this->region_id, "publication" => 1);
		$shop_m = Models\Shops::all($options);
		//print_r($shops);
		
		$this->createShopNode();
		
		foreach ($shop_m as $key => $val)
		{
			$this->displayShops($val);
		}
	}
	
	/**
	 * выставляем на страницу основную ноду магазинов
	 */
	private function createShopNode()
	{
		$this->shops="";
		$this->shops->addAttribute("region_id", $this->region_id);
	}
	
	
	private function displayShops($val)
	{
		$shop = $this->shops->addChild("shop");
		$shop->addChild("shop_id", $val->shop_id);
		$shop->addChild("shop_name", ToUTF($val->name));
		$metro = $shop->addChild("metro");
		
		$this->addStantion($metro, $val);
		
		$this->addAdress($shop, $val);
		
		$shop->addChild("day_hours", ToUTF($val->day_hours));
		$shop->addChild("holiday_hours", ToUTF($val->holyday_hours));
		$shop->addChild("phone", ToUTF($val->phone));
		$this->addCoords($shop, $val);
		
		$shop->addChild("wayTo", StripTags($val->howto));
		$shop->addChild("zoom", $val->map_zoom);
		
		$this->addImage($shop, $val);
	}
	
	
	private function addAdress(&$node, $val)
	{
		$adresss = ToUTF($val->address);
		$pattern = array("/^МО,\s*?г.\s*?/","/^МО,\s*?/","/^ул.\s*/",
						"/^г.\s*?Москва,\s*?ул.\s*/",
						"/^г.\s*?Москва,\s*/", "/^Москва,\s*ул.\s*/", 
						"/^Москва,\s*/","/^г.\s*/","/^Коммунальная зона\s*/", "/^\s+?/");
		
		$node->addChild("address", preg_replace($pattern, '', $adresss));
	}
	
	private function addStantion(&$node, $val)
	{
		if($val->metro)
		{
			$c = explode(",", $val->metro);
			if(count($c)>1) 
			{
				foreach ($c as $v) 
				{
					$node->addChild("station", preg_replace("/^\s+?/i", "", ToUTF($v)));
				}
			}
			else $node->addChild("station", ToUTF($val->metro));
		}
	}
	
	private function addCoords(&$node, $val)
	{
		$coordinates = $node->addChild("coordinates");
		$val->coordinates();
		$coordinates->addChild("longitude", $val->longitude);
		$coordinates->addChild("latitude", $val->latitude);
	}
	
	private function addImage(&$node, $val)
	{
		$images = $node->addChild("images"); 
		$image = $images->addChild("image", "http://www.mvideo.ru/imgs/shop/face/big_$val->p.gif"); 
		$image->addAttribute("width", "500"); 
		$image->addAttribute("height", "375"); 
	}
}