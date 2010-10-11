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
	//use Routing;
	
class ControllerShops extends Template{
	
	public function index( $region_id = 0 )
	{
		$regions = $this->Set("shops");
		for($i=0; $i<4; $i++)
		{
			$region = $regions->addChild("shop");
				$region->addChild("shop_id", "1");
				$region->addChild("shop_name", "2");
				$region->addChild("metro", "3");
				$region->addChild("address", "1");
				$region->addChild("day_hours", "2");
				$region->addChild("holiday_hours", "3");
				$region->addChild("phone", "1");
				$region->addChild("coordinates", "2");
				$region->addChild("wayTo", "3");
				$region->addChild("zoom", "1");
				$images = $region->addChild("images");
					$image = $images->addChild("image", "width", "height");
					$image->addAttribute("width", "");
					$image->addAttribute("height", "");
		}
	}
}