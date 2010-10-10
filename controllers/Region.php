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
	//use Routing;
	
class ControllerRegion extends Template{
	
	public function index( $region_id = 0 )
	{
		$regions = $this->addChild("regions");
		for($i=0; $i<4; $i++)
		{
			$region = $regions->addChild("region");
			
			$region->addChild("id", "1");
			$region->addChild("name", "2");
			$region->addChild("coordinates", "3");
			
			
			
		}
	}
}