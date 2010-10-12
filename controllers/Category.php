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
	use Models;
	
class ControllerCategory extends Template{
	
	public function index( $array )
	{
		
		list($region_id, $category_id)=$array;
		
		$options = array('parent_id' => $category_id);
		$parrents = Models\Category::find('first', array('conditions' => "category_id = $category_id"));
		//print_r($parrents->attributes());
		if(!$category_id)
		{
			$options = array('conditions' => "parent_id is null");
			$c_name = "Список категорий";
		}
		else
		{
			$options = array('parent_id' => $category_id);
			$c_name = ToUTF($parrents->name);
		}
		
		$categorys = Models\Category::find('all', $options);
		$parrent = $this->Set("parent_category");
		$parrent->addChild("category_id", $category_id);
		$parrent->addChild("category_name", $c_name);
			
		$categories = $this->Set("categories");
		$categories->addAttribute("category_id", $category_id);
		$categories->addAttribute("category_name", $c_name);
		if($categorys)
		{
			foreach ($categorys as $key => $val)
			{
				$amount = Models\Category::count(array('conditions' => "parent_id = $val->category_id"));
				
				$category = $categories->addChild("category");
				$category->addChild("category_id", $val->category_id);
				$category->addChild("category_name", ToUTF($val->name));
				$category->addChild("amount", $amount); 
				$icon = $category->addChild("category_icon"); #TODO откуда брать ???
				$icon->addAttribute("width", "");
				$icon->addAttribute("height", "");
			}
		}
		else
			$this->products($region_id, $category_id, $parrents);
	}
	
	private static function products( $region_id, $category_id, $parrents)
	{
		#$join="RIGHT JOIN varez_$region_id w ON(descriptionlist.warecode = a.warecode)";
		#$options = array('join' => $join);
		//print $parrents->dirid;
		$products = Models\Category::warez($region_id, $parrents);
		print_r($products);
		
	}
}