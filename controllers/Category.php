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
	private $page;
	private static $GlobalConfig = array();
	
	
	public function index( $array )
	{
		$GlobalConfig=array();
		$rfile = dirname(dirname(dirname($_SERVER["SCRIPT_FILENAME"])));
		
		list($this->region_id, $this->category_id, $this->actions, $this->searches, $this->page)=$array;
		
		$GlobalConfig['RegionID']=$this->region_id;
		require_once $rfile . '/lib/federal_info.lib.php';
		require_once $rfile . '/lib/sdirs.lib.php';
		
		self::$GlobalConfig = $GlobalConfig;
		
		$this->options = array('parent_id' => $this->category_id);
		$this->parents = Models\Category::find('first', array('conditions' => "category_id = $this->category_id"));
		
		
		if($this->category_id >=0 && !$this->searches && $this->actions < 0)
		{
			$this->parent_node();
			if($this->category_id == 0)
				$this->category = $this->rootCategories();
			if($this->category_id >0)
				$this->category = Models\Category::find('all', $this->options);
		}
		if($this->searches)
			$this->category = $this->search();
		if($this->actions > 0)
			$this->category = $this->action();
		
		//print_r($this->category);
		//$condition = "";
		//$categoryssss = Models\Category::getWarezAction($this->region_id, $this->action_val, $condition);
		//print_r($categoryssss);
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
				if($this->actions > 0)
				{
					$amount = count(Models\Warez::find_by_sql('select * from `warez_' .$this->region_id . '` 
									where warecode in ('.implode(",", $this->action_val).') and DirID = '.$val->dirid  ));
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
	

	private function productes()
	{
		if($this->actions > 0 && $this->action_val)
			$this->parents->dirid .= " and warecode in (".implode(",", $this->action_val).")";#$this->parents->search
		
		if($this->searches)
		{
			$this->parent_node();
			$this->parents->grid = " ware like \"%$this->searches%\" or FullName like \"%$this->searches%\" ";
		}
		//var_dump($this->parents);
		if($this->parents)
		{
			$page = $this->page;
			if($this->page > 0)
				$page = ($this->page -1)*20;
			
			$productes_count = count(Models\Warez::getWarez($this->region_id, $this->parents, False));
			$productes_m = Models\Warez::getWarez($this->region_id, $this->parents, $page);
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
			if($this->page)
			{
				$this->pages="";
				$this->pages->addChild("amount", $productes_count);
				$this->pages->addChild("onpage", "20");
				$this->pages->addChild("page", $this->page);
			}
			foreach ($productes_m as $key => $val)
			{
				if (!in_array($val->grid, $grid))
					$grid[]=$val->grid;
				if (!in_array($val->mark, $markid))
					$markid[]=$val->mark;
				//add products

				$product = $this->products->addChild("product");
				$product->addChild("product_id", ToUTF($val->warecode));
				$product->addChild("title", StripTags($val->fullname));
				$val->getDesctiptions();
				$product->addChild("description", StripTags($val->description));
				//$rewiews = Models\Reviews::first(array('select' => 'count(rating) c, sum(rating) s', 'conditions' => array('warecode = ?', $val->warecode)));
				$val->getRatingRev();
				$product->addChild("rating", $val->rating);
				$product->addChild("reviews_num", $val->reviews);
				$product->addChild("inet_price", $val->inetprice);
				$product->addChild("old_price", $val->oldprice);
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
		elseif($this->category_id<1000)
		{
			$cond = array('conditions' => "parent_id is null");
			$cat_parrent_name = $this->parent_name = "Список категорий";
			$cat_parrent_id = $this->parent_id = 0;
			if(array_key_exists($this->category_id, self::$GlobalConfig['smenu']))
			{
				$cond =array('conditions' => 
							array('parent_id is null and dirid in (?)', 
								self::$GlobalConfig['smenu'][$this->category_id]['dirs']
								)
							);
				$this->parent_name = ToUTF(self::$GlobalConfig['smenu'][$this->category_id]['name']);
			}
			$this->options = $cond;
		}
		else
		{
			/**
			 * проверяем parent текущей категории
			 * и выставляем id и name у родительского нода
			 */
			$cat_parrent_id = 0;
			$cat_parrent_name = "Список категорий";
			if(!$this->parents->parent_id)
			{
				foreach (self::$GlobalConfig['smenu'] as $key => $value) {
					if(in_array($this->parents->dirid, self::$GlobalConfig['smenu'][$key]['dirs']))
					{
						$cat_parrent_id = ToUTF($key);
						$cat_parrent_name = ToUTF(self::$GlobalConfig['smenu'][$key]['name']);
						break;
					}
					
				}
				
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
		$catid = "";
		if($this->parents)
			if($this->parents->grid)
				$catid = $this->category_id;
		$category = Models\Category::findByNameCategory($this->region_id, $this->searches, $catid);
		//print_r($category);
		if(!$category)
		{
			
			//$this->products($region_id, $category_id, $parents, $array);
			return array();
		}
		else
		{
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
				$action = 0;
				return $this->localActions();
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
			default:
				$action = $this->actions;
				break;
		}
		//print $action;
		$act = Models\Actions::first(array("segment_id"=>$action, "hidden"=>0));
		//print_r($act);
		if($act)
		{
			$url = str_replace("/", "", $act->segment_name);//link
			$imgfile = "imgs/action/header_$url.jpg";
			
			$this->action = "";
			$this->actionImage($imgfile);
			
			$this->action->addChild("description", ToUTF($act->segment_info));
			$this->action->addChild("url", "http://www.mvideo.ru/".str_replace("_", "-", $act->segment_name)
																."/?ref=left_bat_". $act->segment_name);
			
			$categorys = $this->putActions($act->segment_name);
			return $categorys;
		}
	}
	private function putActions($name)
	{
		
		$segment = Models\Segments::find('all', 
							array('select' => 'warecode', 
								'conditions' =>"region_id=$this->region_id and segment_name='$name'"));
			
		
		foreach ($segment as $val)
		{
			$this->action_val[] = $val->warecode;
		}
		//print $region_id. $array;
		
		if(!$this->category_id)
		{
			$condition = " and c.parent_id is null ";
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
	/**
	 * создает ноду с картинкой для акции
	 * @param unknown_type $img
	 */
	private function actionImage($img)
	{
		$imgdir = dirname(dirname($_SERVER["SCRIPT_FILENAME"]));
		
		$fimgs = "http://www.mvideo.ru/$img";
		//print $imgdir."/".$imgfile;
		if(file_exists($imgdir."/".$img))
		{
			$imgsize = getimagesize ($imgdir."/".$img);
			//print_r($imgsize);
		
			//создаем картинку
			$images = $this->action->addChild("image", $fimgs);
			//задаем размеры
			
			$images->addAttribute("width", $imgsize[0]);
			$images->addAttribute("height", $imgsize[1]);
		}
	}
	/**
	 * текущая федеральная акция
	 */
	private function localActions()
	{
		$time = time();
		//$keys = array_keys(self::$GlobalConfig['fed_act']);
		foreach (self::$GlobalConfig['fed_act'] as $key => $val) 
		{
			if($key < $time)
			{
				if($val['end_date']>=$time)
				{
					$url = str_replace("/", "", $val['link']);//link
					$imgfile = "imgs/action/main/$url.jpg";
					$this->action = "";
					$this->actionImage($imgfile);
					
					$this->action->addChild("description", ToUTF($val['name']));//descr
					
					$this->action->addChild("url", "http://www.mvideo.ru/".$url
													."/?ref=home_promo_". $url);
					return $this->putActions($url);
				}
			}
		}
		
	}
	
	private function rootCategories()
	{
		
		
		$this->categories="";
		$this->categories->addAttribute("category_id", $this->category_id);
		$this->categories->addAttribute("category_name", $this->parent_name);
		foreach (self::$GlobalConfig['smenu'] as $key => $value) 
		{
			$amount = count(Models\Category::find('all', array('conditions' => 
									array('parent_id is null and dirid in (?)', $value['dirs'])
									)));
			$category = $this->categories->addChild("category");
			$category->addChild("category_id", $key);
			$category->addChild("category_name", ToUTF($value['name']));
			$category->addChild("amount", $amount); 
			$icon = $category->addChild("category_icon", "http://www.mvideo.ru/imgs/catalog/dir_$key.gif"); #TODO откуда брать иконку категории???
			$icon->addAttribute("width", "50");
			$icon->addAttribute("height", "50");
		}
		//exit();
		return False;
		
	}
}