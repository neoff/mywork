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
		
		list($region_id, $category_id, $action, $search)=$array;
		//print_r($array);
		$options = array('parent_id' => $category_id);
		$parents = Models\Category::find('first', array('conditions' => "category_id = $category_id"));
		//print_r($parents->attributes());
		
		if(!$category_id || $category_id<0 )
		{
			$options = array('conditions' => "parent_id is null");
			$c_parrent_name = $c_name = "Список категорий";
			$c_parrent_id = $c_id = 0;
			$category_id = 0;
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
		
		if($search)
		{
			$this->Set("search", ToUTF($search));
			$categorys = Models\Warez::findByNameCategory($region_id, $search);
			if(count($categorys)==1 || $category_id>0)
			{
				//$this->products($region_id, $category_id, $parents, $array);
				$categorys = array();
			}
			//print_r($categorys);
		}
		
		if($action>0)
		{
			switch ((int)$action)
			{
				case 1:
					$action = 6;
					break;
				case 3:
					$action = 7;
					break;
				case 4:
					$action = 24;
					break;
				case 5:
					$action = 25;
					break;
				case 6:
					$action = 29;
					break;
				case 7:
					$action = 28;
					break;
			}
			//print $action;
			$act = Models\Actions::first(array("segment_id"=>$action, "hidden"=>0));
			//print_r($act);
			$actions = $this->Set("action");
			$images = $actions->addChild("image", "http://www.mvideo.ru/imgs/test.jpg");
			$images->addAttribute("width", "150");
			$images->addAttribute("height", "150");
			$actions->addChild("description", ToUTF($act->segment_info));
			$actions->addChild("url", "http://www.mvideo.ru/".str_replace("_", "-", $act->segment_name)
																."/?ref=left_bat_". $act->segment_name);
			
			
			$segment = Models\Segments::find('all', 
							array('select' => 'warecode', 
								'conditions' =>"region_id=$region_id and segment_name='$act->segment_name'"));
			
			$action=array();
			foreach ($segment as $val)
			{
				$action[] = $val->warecode;
			}
			//print $region_id. $array;
			
			if(!$category_id)
			{
				$condition = "";
				$c_parrent_id = 0;
				$c_parrent_name = $c_name = "Список категорий";
				$categorys = Models\Warez::getWarezAction($region_id, $action, $condition);
			}
			else
			{
				//$this->products($region_id, $category_id, $parents, $action);
				$categorys = array();
			}
			
		}
		
		//print_r($categorys);
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
					$amount = count(Models\Warez::getWarez($region_id, $ids));
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
			$this->products($region_id, $category_id, $parents, $action, $search);
	}
	
	private function products( $region_id, $category_id, $parents, $actions = "", $search="")
	{
		
		if($actions > 0)
		{
			$parents->grid .= " and warecode in (".implode(",", $actions).")";
		}
		
		if($search)
		{
			$parents->grid .= " and ware like \"%$search%\" or FullName like \"%$search%\" ";
		}
		
		if($parents)
		{
			$productes = Models\Warez::getWarez($region_id, $parents);
			//print_r($productes);
			$c_name = ToUTF($parents->name);
			
			
			//add params
			$params = $this->Set("params");
			$grid=array();
			$markid = array();
			//print_r($productes);
			//$markid = array();
			
			$param_m = $params->addChild("param"); #TODO узнать в какой таблице брать список параметров
			$param_m->addAttribute("param_name", "mark");
			$param_m->addAttribute("title", "Производители");
			$param_m->addAttribute("current_value", "0");
			$option_p = $param_m->addChild("option", "Все производители");
			$option_p->addAttribute("value", "0");
			//2
			$param_g = $params->addChild("param"); 
			$param_g->addAttribute("param_name", "grid");
			$param_g->addAttribute("title", "Группы");
			$param_g->addAttribute("current_value", "0");
			$option_g = $param_g->addChild("option", "Все группы");
			$option_g->addAttribute("value", "0");
			
			
			foreach ($productes as $key => $val)
			{
				if (!in_array($val->grid, $grid))
					$grid[]=$val->grid;
				if (!in_array($val->mark, $markid))
					$markid[]=$val->mark;
				//add products
				$products = $this->Set("products");
				$products->addAttribute("category_id", $category_id);
				$products->addAttribute("category_name", $c_name);
				$product = $products->addChild("product");
				$product->addChild("product_id", ToUTF($val->warecode));
				$product->addChild("title", ToUTF($val->fullname));
				$description = Models\Description::first(array("warecode"=>$val->warecode));
				if($description)
					$description = $description->reviewtext;
				//print StripTags($description)."<br/>\n";
				$product->addChild("description", StripTags($description));
				$rewiews = Models\Reviews::first(array('select' => 'count(rating) c, sum(rating) s', 'conditions' => array('warecode = ?', $val->warecode)));
				$product->addChild("rating", $rewiews->c); #TODO где брать рейтинг?
				$product->addChild("small_price", $val->inetprice);
				$product->addChild("price", $val->price);
				$image = $product->addChild("image", "http://www.mvideo.ru/Pdb/$val->warecode.jpg"); #TODO где взять картинка для продукта
				$image->addAttribute("width", "180");
				$image->addAttribute("height", "180");
			}
			foreach($grid as $val)
			{
				$m_group = Models\Groups::find('first', array("grid"=>$val));
				if($m_group){
					//print ToUTF($m_marks->markname);
					$option_g = $param_g->addChild("option", ToUTF($m_group->grname));
					$option_g->addAttribute("value", $val);
				}
			}
			foreach($markid as $val)
			{
				$m_marks = Models\Marks::find('first', array("markid"=>$val));
				if($m_marks){
					//print ToUTF($m_marks->markname);
					$option_m = $param_m->addChild("option", ToUTF($m_marks->markname));
					$option_m->addAttribute("value", $val);
				}
			}
		}
	}
	public function actions($array)
	{
		list($region_id, $category_id, $action)=$array;
		$actions = $this->Set("actions");
		$acts = Models\Actions::all(array("hidden"=>0));
		foreach ($acts as $key => $val) 
		{
			$action = $actions->addChild("action");
			$action->addChild("id", $val->segment_id);
			$images = $action->addChild("image", "http://www.mvideo.ru/imgs/test.jpg");
			$images->addAttribute("width", "150");
			$images->addAttribute("height", "150");
			$action->addChild("description", ToUTF($val->segment_info));
			$action->addChild("url", "http://www.mvideo.ru/".str_replace("_", "-", $val->segment_name)."/?ref=left_bat_". $val->segment_name);
		}
		
		
	}
}