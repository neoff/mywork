<?php
/**  
 * 
 * 
 * @package    controller
 * @subpackage Actions
 * @since      13.10.2010 13:24:53
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
		$parents = Models\Category::find('first', array('conditions' => "category_id = $category_id"));
		//print_r($parents->attributes());
		if(!$category_id )
		{
			$options = array('conditions' => "parent_id is null");
			$c_parrent_name = $c_name = "Список категорий";
			$c_parrent_id = $c_id = 0;
		}
		else
		{
			if(!$parents->parent_id)
			{
				$c_parrent_id = 0;
				$c_parrent_name = "Список категорий";
			}else
			{
				$c_parrent_id = $parents->parent_id;
				$p = Models\Category::first(array('category_id' => $c_parrent_id));
				$c_parrent_name = ToUTF($p->name);
			}
			$options = array('parent_id' => $category_id);
			$c_name = ToUTF($parents->name);
			$c_id = $parents->category_id;
			
			
			
			
		}
		
		$categorys = Models\Category::find('all', $options);
		$parrent = $this->Set("parent_category");
		$parrent->addChild("category_id", $c_parrent_id);
		$parrent->addChild("category_name", $c_parrent_name);
		
		if($categorys)
		{
			$categories = $this->Set("categories");
			$categories->addAttribute("category_id", $category_id);
			$categories->addAttribute("category_name", $c_name);
			
			foreach ($categorys as $key => $val)
			{
				$amount = Models\Category::count(array('conditions' => "parent_id = $val->category_id"));
				if(!$amount) 
				{
					$ids = new SetId($val->dirid, $val->classid, $val->grid);
					$amount = count(Models\Category::warez($region_id, $ids));
				}
				$category = $categories->addChild("category");
				$category->addChild("category_id", $val->category_id);
				$category->addChild("category_name", ToUTF($val->name));
				$category->addChild("amount", $amount); 
				$icon = $category->addChild("category_icon"); 
				$icon->addAttribute("width", "");
				$icon->addAttribute("height", "");
			}
		}
	}
}