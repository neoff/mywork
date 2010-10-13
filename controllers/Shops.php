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
	
class ControllerShops extends Template{
	
	public function index( $shop_id = array(0, false, false ) )
	{
		list($region_id, $width, $height) = $shop_id;
		
		$options = array('region_id' => $shop_id);
		
		$model = Models\Shops::all($options);
		//print_r($shops);
		$shops = $this->Set("shops");
		$shops->addAttribute("region_id", $shop_id);
		list($longitude, $latitude) = array("", "");
		foreach ($model as $key => $val)
		{
			$shop = $shops->addChild("shop");
			$shop->addChild("shop_id", $val->shop_id);
			$shop->addChild("shop_name", ToUTF($val->shop_name));
			$shop->addChild("metro", ToUTF($val->metro));
			$shop->addChild("address", ToUTF($val->address));
			$shop->addChild("day_hours", ToUTF($val->day_hours));
			$shop->addChild("holiday_hours", ToUTF($val->holyday_hours));
			$shop->addChild("phone", ToUTF($val->phone));
			$coordinates = $shop->addChild("coordinates");
			if($val->map_latlng)
			{
				$coord = explode(",", $val->map_latlng);
				if(count($coord)==2) list($longitude, $latitude) = array($coord[0], $coord[1]);
			}
			$coordinates->addChild("longitude", $longitude);
			$coordinates->addChild("latitude", $latitude);
			$shop->addChild("wayTo", StripTags($val->howto));
			$shop->addChild("zoom", $val->map_zoom);
			$images = $shop->addChild("images"); 
			$image = $images->addChild("image", ""); #TODO узнать где брать image
			$image->addAttribute("width", ""); 
			$image->addAttribute("height", ""); 
		}
	}
}