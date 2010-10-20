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
	use Template;
	
class SetId{
	public function __set($name, $val)
	{
		$this->$name = $val;
	}
	
	public function __construct($dirid, $classid, $grid)
	{
		
		list($this->dirid, $this->classid, $this->grid) = array($dirid, $classid, $grid);
	}
}
class ControllerCategory extends Template\Template{
	
	private $parent_name;
	private $parent_id;
	private $region_id;
	private $category_id;
	private $actions;
	private $action_val = array();
	private $searches;
	private $parents;
	private $category;
	private $options;
	
	
	public function index( $array )
	{
		
		list($this->region_id, $this->category_id, $this->actions, $this->searches)=$array;
		$this->options = array('parent_id' => $this->category_id);
		$this->parents = Models\Category::find('first', array('conditions' => "category_id = $this->category_id"));
		
		
		if($this->category_id >=0 && !$this->searches && $this->actions < 0)
			$this->parent_node();
		$this->category = Models\Category::find('all', $this->options);
		if($this->searches)
			$this->category = $this->search();
		if($this->actions > 0)
			$this->category = $this->action();
		
		
		if($this->category)
		{
			
			$this->categories="";
			$this->categories->addAttribute("category_id", $this->category_id);
			$this->categories->addAttribute("category_name", $this->parent_name);
			
			foreach ($this->category as $key => $val)
			{
				$amount = Models\Category::count(array('conditions' => "parent_id = $val->category_id"));
				if(!$amount) 
				{
					$ids = new SetId($val->dirid, $val->classid, $val->grid);
					$amount = count(Models\Warez::getWarez($this->region_id, $ids));
				}
				$category = $this->categories->addChild("category");
				$category->addChild("category_id", $val->category_id);
				$category->addChild("category_name", ToUTF($val->name));
				$category->addChild("amount", $amount); 
				$icon = $category->addChild("category_icon", "http://www.mvideo.ru/imgs/catalog/dir_$val->dirid.gif"); #TODO откуда брать иконку категории???
				$icon->addAttribute("width", "50");
				$icon->addAttribute("height", "50");
			}
		}
		else
			$this->productes();
	}
	

	public function productes()
	{
		if($this->actions > 0)
			$this->parents->grid .= " and warecode in (".implode(",", $this->action_val).")";
		
		if($this->searches)
			$this->parents->grid .= " and ware like \"%$this->searches%\" or FullName like \"%$this->searches%\" ";
			
		if($this->parents)
		{
			$productes_m = Models\Warez::getWarez($this->region_id, $this->parents);
			//print_r($productes);
			$c_name = ToUTF($this->parents->name);
			
			
			//add params
			$params = $this->params="";
			$grid=array();
			$markid = array();
			//print_r($productes);
			//$markid = array();
			
			$param_m = $this->params->addChild("param"); 
			$param_m->addAttribute("param_name", "mark");
			$param_m->addAttribute("title", "Производители");
			$param_m->addAttribute("current_value", "0");
			$option_m = $param_m->addChild("option", "Все производители");
			$option_m->addAttribute("value", "0");
			//2
			$param_g = $this->params->addChild("param"); 
			$param_g->addAttribute("param_name", "grid");
			$param_g->addAttribute("title", "Группы");
			$param_g->addAttribute("current_value", "0");
			$option_g = $param_g->addChild("option", "Все группы");
			$option_g->addAttribute("value", "0");
			
			$this->products="";
			$this->products->addAttribute("category_id", $this->category_id);
			$this->products->addAttribute("category_name", $c_name);
			foreach ($productes_m as $key => $val)
			{
				if (!in_array($val->grid, $grid))
					$grid[]=$val->grid;
				if (!in_array($val->mark, $markid))
					$markid[]=$val->mark;
				//add products

				$product = $this->products->addChild("product");
				$product->addChild("product_id", ToUTF($val->warecode));
				$product->addChild("title", ToUTF($val->fullname));
				$val->getDesctiptions();
				$product->addChild("description", StripTags($val->description));
				//$rewiews = Models\Reviews::first(array('select' => 'count(rating) c, sum(rating) s', 'conditions' => array('warecode = ?', $val->warecode)));
				$product->addChild("rating", $val->rating); #TODO где брать рейтинг?
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
	
	/**
	 * устанавливаем ноду parent_category
	 * проверяем название и предыдущую категорию 
	 * 
	 * @param int $category_id
	 * @param object $parents_m
	 * @return array $options
	 */
	private function parent_node()
	{
		/**
		 * ставим заголовки блока parents
		 * если parent = 0 то ставим список всех категорий
		 */
		if(!$this->category_id || $this->category_id<0 )
		{
			$this->options = array('conditions' => "parent_id is null");
			$cat_parrent_name = $this->parent_name = "Список категорий";
			$cat_parrent_id = $this->parent_id = 0;
			$this->category_id = 0;
		}
		else
		{
			/**
			 * проверяем parent текущей категории
			 * и выставляем id и name у родительского нода
			 */
			if(!$this->parents->parent_id)
			{
				$cat_parrent_id = 0;
				$cat_parrent_name = "Список категорий";
			}else
			{
				$cat_parrent_id = $this->parents->parent_id;
				$p = Models\Category::first(array('category_id' => $cat_parrent_id));
				$cat_parrent_name = ToUTF($p->name);
			}
			$this->options = array('parent_id' => $this->category_id);
			$this->parent_name = ToUTF($this->parents->name);
			$this->parent_id = $this->category_id;
		}
		$this->parent_category="";
		$this->parent_category->addChild("category_id", $cat_parrent_id);
		$this->parent_category->addChild("category_name", $cat_parrent_name);
		
		return $this->options;
	}
	
	private function search()
	{
		$this->search=ToUTF($this->searches);
		$category = Models\Warez::findByNameCategory($this->region_id, $this->searches, $this->category_id);
		//print_r($category);
		if(!$category)
		{
			
			//$this->products($region_id, $category_id, $parents, $array);
			return array();
		}
		else
		{
			$this->parent_node();
			if(count($category)==1)
			{
				return array();
			}
		}
		return $category;
	}
	
	private function action()
	{
		switch ((int)$this->actions)
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
		if($act)
		{
			$this->action = "";
			$images = $this->action->addChild("image", "http://www.mvideo.ru/imgs/test.jpg");
			$images->addAttribute("width", "150");
			$images->addAttribute("height", "150");
			$this->action->addChild("description", ToUTF($act->segment_info));
			$this->action->addChild("url", "http://www.mvideo.ru/".str_replace("_", "-", $act->segment_name)
																."/?ref=left_bat_". $act->segment_name);
			
			$segment = Models\Segments::find('all', 
							array('select' => 'warecode', 
								'conditions' =>"region_id=$this->region_id and segment_name='$act->segment_name'"));
			
			
			foreach ($segment as $val)
			{
				$this->action_val[] = $val->warecode;
			}
			//print $region_id. $array;
			
			if(!$this->category_id)
			{
				$condition = "";
				//$this->parrent_id = 0;
				//$this->parrent_name = $c_name = "Список категорий";
				$categorys = Models\Category::getWarezAction($this->region_id, $this->action_val, $condition);
				//print_r($categorys);
			}
			else
			{
				$categorys = array();
			}
			$this->parent_node();
			return $categorys;
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