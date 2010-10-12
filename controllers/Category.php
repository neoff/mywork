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
	
class SetId{
	public $dirid;
	public $classid;
	public $grid;
	
	public function __construct($dirid, $classid, $grid)
	{
		
		list($this->dirid, $this->classid, $this->grid) = array($dirid, $classid, $grid);
	}
}
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
			
		$categories = $this->Set("categories");
		$categories->addAttribute("category_id", $category_id);
		$categories->addAttribute("category_name", $c_name);
		if($categorys)
		{
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
				$icon = $category->addChild("category_icon"); #TODO откуда брать ???
				$icon->addAttribute("width", "");
				$icon->addAttribute("height", "");
			}
		}
		else
			$this->products($region_id, $category_id, $parents);
	}
	
	private function products( $region_id, $category_id, $parents)
	{
		$productes = Models\Category::warez($region_id, $parents);
		
		//add params
		$params = $this->Set("params");
		$param = $params->addChild("param"); #TODO узнать в какой таблице
		$param->addAttribute("param_name", "");
		$param->addAttribute("title", "");
		$param->addAttribute("current_value", "");
		$option = $param->addChild("option", "");
		$option->addAttribute("value", "");
		
		foreach ($productes as $key => $val)
		{
			//add products
			$products = $this->Set("products");
			$products->addAttribute("category_id", "");
			$products->addAttribute("category_name", "");
			$product = $products->addChild("product");
			$product->addChild("product_id");
			$product->addChild("title");
			$product->addChild("description");
			$product->addChild("rating");
			$product->addChild("small_price");
			$product->addChild("price");
			$image = $product->addChild("image");
			$image->addAttribute("width", "");
			$image->addAttribute("height", "");
		}
	}
}