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
	use Template;
	
class ControllerRegion extends Template\Template{
	
	public function index( $region_id = 0 )
	{
		$region_m = Models\Regions::all();
		$this->regions = "";
		
		foreach ($region_m as $key => $val)
		{
			if($val->id != 8)
			{
			$region = $this->regions->addChild("region");
			
				$region->addChild("region_id", $val->id );
				$region->addChild("region_name", ToUTF($val->region_name));
				$coordinates = $region->addChild("coordinates");
				$val->coordinates();
				$coordinates->addChild("longitude", $val->longitude);
				$coordinates->addChild("latitude", $val->latitude);
			}
			
		}
	}
}