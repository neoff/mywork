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
		$region = Models\Regions::all();
		//$region = Models\Regions::first();
		//print_r($region);
		//foreach ($region as $key => $val) {
		//	echo $val->region_id;
		//}
		$regions = $this->Set("regions");
		foreach ($region as $key => $val)
		{
			$region = $regions->addChild("region");
			
			$region->addChild("region_id", $val->region_id );
			$region->addChild("region_name", ToUTF($val->region_name));
			$region->addChild("coordinates", "3");
			
			
			
		}
	}
}