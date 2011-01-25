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
	
	public function index( $shop_id = array(0, false, false ) )
	{
		list($shop_id, $width, $height) = $shop_id;
		
		$options = array('region_id' => $shop_id, "publication" => 1);
		$shop_m = Models\Shops::all($options);
		//print_r($shops);
		$this->shops="";
		$this->shops->addAttribute("region_id", $shop_id);
		list($longitude, $latitude) = array("", "");
		
		foreach ($shop_m as $key => $val)
		{
			$shop = $this->shops->addChild("shop");
			$shop->addChild("shop_id", $val->shop_id);
			$shop->addChild("shop_name", ToUTF($val->name));
			$metro = $shop->addChild("metro");
			if($val->metro)
			{
				$c = explode(",", $val->metro);
				if(count($c)>1) 
				{
					foreach ($c as $v) {
						$metro->addChild("station", preg_replace("/^\s+?/i", "", ToUTF($v)));
					}
				}
				else $metro->addChild("station", ToUTF($val->metro));
			}
			$shop->addChild("address", ToUTF($val->address));
			$shop->addChild("day_hours", ToUTF($val->day_hours));
			$shop->addChild("holiday_hours", ToUTF($val->holyday_hours));
			$shop->addChild("phone", ToUTF($val->phone));
			$coordinates = $shop->addChild("coordinates");
			$val->coordinates();
			//if($val->longitude && $val->latitude)
			//{
				$coordinates->addChild("longitude", $val->longitude);
				$coordinates->addChild("latitude", $val->latitude);
			//}
			$shop->addChild("wayTo", StripTags($val->howto));
			$shop->addChild("zoom", $val->map_zoom);
			$images = $shop->addChild("images"); 
			$image = $images->addChild("image", "http://www.mvideo.ru/imgs/shop/face/big_$val->p.gif"); #TODO узнать где брать image
			$image->addAttribute("width", "500"); 
			$image->addAttribute("height", "375"); 
		}
	}
}