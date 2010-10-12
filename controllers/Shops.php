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
	
	public function index( $shop_id = 0 )
	{
		$options = array('region_id' => $shop_id);
		
		$shops = Models\Shops::all($options);
		//print_r($shops);
		$regions = $this->Set("shops");
		foreach ($shops as $key => $val)
		{
			$region = $regions->addChild("shop");
				$region->addChild("shop_id", $val->shop_id);
				$region->addChild("shop_name", ToUTF($val->shop_name));
				$region->addChild("metro", ToUTF($val->metro));
				$region->addChild("address", ToUTF($val->address));
				$region->addChild("day_hours", ToUTF($val->day_hours));
				$region->addChild("holiday_hours", ToUTF($val->holyday_hours));
				$region->addChild("phone", ToUTF($val->phone));
				$region->addChild("coordinates", $val->map_latlng);
				$region->addChild("wayTo", StripTags($val->howto));
				$region->addChild("zoom", $val->map_zoom);
				$images = $region->addChild("images"); 
					$image = $images->addChild("image", ""); #TODO узнать где брать image
					$image->addAttribute("width", ""); 
					$image->addAttribute("height", ""); 
		}
	}
}