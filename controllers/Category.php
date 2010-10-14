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
		
		list($region_id, $category_id, $product_id)=$array;
		
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
				$icon = $category->addChild("category_icon", "http://www.mvideo.ru/imgs/catalog/dir_$val->dirid.gif"); #TODO откуда брать иконку категории???
				$icon->addAttribute("width", "50");
				$icon->addAttribute("height", "50");
			}
		}
		else
			$this->products($region_id, $category_id, $parents);
	}
	
	private function products( $region_id, $category_id, $parents)
	{
		
		
		$productes = Models\Category::warez($region_id, $parents);
		//print_r($productes);
		$c_name = ToUTF($parents->name);
		
		
		//add params
		$params = $this->Set("params");
		$param = $params->addChild("param"); #TODO узнать в какой таблице брать список параметров
		$param->addAttribute("param_name", "");
		$param->addAttribute("title", "");
		$param->addAttribute("current_value", "");
		$option = $param->addChild("option", "");
		$option->addAttribute("value", "");
		
		foreach ($productes as $key => $val)
		{
			//add products
			$products = $this->Set("products");
			$products->addAttribute("category_id", $category_id);
			$products->addAttribute("category_name", $c_name);
			$product = $products->addChild("product");
			$product->addChild("product_id", ToUTF($val->warecode));
			$product->addChild("title", ToUTF($val->ware));
			$description = Models\Description::first(array("warecode"=>$val->warecode));
			if($description)
				$description = $description->reviewtext;
			//print StripTags($description)."<br/>\n";
			$product->addChild("description", StripTags($description));
			$rewiews = Models\Reviews::first(array('select' => 'count(grade) c, sum(grade) s', 'conditions' => array('warecode = ?', $val->warecode)));
			$product->addChild("rating", $rewiews->c); #TODO где брать рейтинг?
			$product->addChild("small_price", $val->inetprice);
			$product->addChild("price", $val->price);
			$image = $product->addChild("image", "http://www.mvideo.ru/Pdb/$val->warecode.jpg"); #TODO где взять картинка для продукта
			$image->addAttribute("width", "180");
			$image->addAttribute("height", "180");
		}
	}
}