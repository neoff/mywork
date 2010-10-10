<?php
/**  
 * 
 * 
 * @package    controllers
 * @subpackage Category
 * @since      08.10.2010 14:00:27
 * @author     enesterov
 * @category   controller
 */


	namespace Controllers;
	//use Routing;
	
class ControllerCategory extends Template{
	
	public function index( $region_id = 0 )
	{
		$parrent = $this->Set("parent_category");
			$parrent->addChild("category_id", "1");
			$parrent->addChild("category_name", "2");
			
		$categories = $this->Set("categories");
		$categories->addAttribute("width", "");
		$categories->addAttribute("height", "");
		for($i=0; $i<4; $i++)
		{
			$category = $categories->addChild("category");
				$category->addChild("category_id", "1");
				$category->addChild("category_name", "2");
				$category->addChild("amount", "3");
				$icon = $category->addChild("category_icon");
				$icon->addAttribute("width", "");
				$icon->addAttribute("height", "");
		}
	}
}