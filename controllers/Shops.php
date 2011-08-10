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
	
class ControllerShops extends InterfaceTemplate{
	
	/**
	 * количество товаров в магазине
	 */
	private static $counts = array(
						0 => "Нет в наличии",
						1 => "Мало",
						2 => "Достаточно",
						3 => "Много"
					);
	
	public function index()
	{
		$this->setVar();
		//$this->region_id = get_key('region_id', 1);
		
		$this->mem_key = 'region_'.$this->region_id;
		
		
		if(!$this->mem_flush && $this->getMemObj())
			return true;
		
		$this->mem_time = 60*60*24*7;
		//var_dump($shop_id);
		//list($shop_id, $width, $height) = $shop_id;
		
		
		$shop_m = Models\Shops::getAllShops($this->region_id);
		//print_r($shops);
		
		$this->createShopNode();
		
		foreach ($shop_m as $key => $val)
		{
			$this->displayShops($val);
		}
	}
	
	/**
	 * 
	 * $counts = array(
	 * 					0 - нет в наличии
	 * 					1 - мало
	 * 					2 - достаточно
	 * 					3 - много
	 * 				)
	 */
	public function shops()
	{
		$rfile = MVIDEO_PATH;
		require_once $rfile . '/lib/classes/ProductCard.class.php';
		
		$this->setVar();
		$this->product_id = get_key('shops_product_id');
		
		$shop_m = Models\Shops::getAllShops($this->region_id);
		$options = array("_warecode"=>$this->product_id);
		list($where, $array) = Models\Warez::SetParam($this->region_id, $options);
		$productes = Models\Warez::sql($this->region_id, $where, $array);
		
		$this->xml->addChild("product_id", $this->product_id);
		$this->createShopNode();
		
		if($shop_m)
		{
			foreach ($shop_m as $key => $val)
			{
				//var_dump($val);
				$shop = parent::displayShops($this->shops, $val);
				
				$shop->addChild("day_hours", ToUTF($val->day_hours));
				$shop->addChild("holiday_hours", ToUTF($val->holyday_hours));
		
				$shop->addChild("count", 'Нет в наличии');
				$options = array('warecode' => $this->product_id, "shop_id" => $val->shop_id);
				$amount = Models\Amounts::find('first', $options);
				if($amount)
				{
					//var_dump($amount);
					if($productes)
					{
						//var_dump($productes);
						$counts = \ProductCard::ShopQtyName($productes[0], $amount->amount);
						$shop->count = self::$counts[$counts];
						//var_dump($counts);
					}
					
					
				}
				
				
				
			}
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
	
	
	
	protected function displayShops($val)
	{
		//$shop = $this->shops->addChild("shop");
		$shop = parent::displayShops($this->shops, $val);
		
		$shop->addChild("phone", ToUTF($val->phone));
		$this->addCoords($shop, $val);
		
		$shop->addChild("wayTo", StripTags($val->howto));
		$shop->addChild("zoom", $val->map_zoom);
		
		$this->addImage($shop, $val);
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