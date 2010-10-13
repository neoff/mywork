<?php
/**  
 * 
 * 
 * @package    controllers
 * @subpackage Region
 * @since      08.10.2010 14:00:27
 * @author     enesterov
 * @category   controller
 */

	namespace Controllers;
	use Models;
	
class ControllerRegion extends Template{
	
	public function index( $region_id = 0 )
	{
		$region_m = Models\Regions::all();
		$regions = $this->Set("regions");
		
		foreach ($region_m as $key => $val)
		{
			$region = $regions->addChild("region");
			
			$region->addChild("region_id", $val->region_id );
			$region->addChild("region_name", ToUTF($val->region_name));
			$coordinates = $region->addChild("coordinates");
//			if($val->map_latlng)
//			{
//				$coord = explode(",", $val->map_latlng);
//				if(count($coord)==2) list($longitude, $latitude) = array($coord[0], $coord[1]);
//			}
			$coordinates->addChild("longitude", "");
			$coordinates->addChild("latitude", "");
			
			
			
		}
	}
}