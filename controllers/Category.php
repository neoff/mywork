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
	
/*	
class SetId{
	public $search;
	
	public function __set($name, $val)
	{
		$this->$name = $val;
	}
	
	public function __construct($dirid, $classid, $grid)
	{
		
		list($this->dirid, $this->classid, $this->grid) = array($dirid, $classid, $grid);
	}
}*/

class ControllerCategory extends Template\Template{
	
	private $parent_name;
	private $parent_id;
	protected $region_id;
	protected $category_id;
	private $actions;
	private $action_val = array();
	private $searches;
	private $parents;
	private $category;
	private $options;
	private $page;
	
	protected static $TmpDir = array();
	protected static $GlobalConfig = array();
	protected static $Brands = array();
	protected static $Dirs = array();
	protected static $Classes = array();
	protected static $Groups = array();
	protected static $Mult = 1000000000;
	protected static $MultC = 100000;
	protected static $MultG = 1;
	
	protected $dir_id;
	protected $class_id;
	protected $group_id;
	
	protected function all_dirs(&$a)
	{
		//SELECT distinct DirID, ClassID, GrID from warez_1;
		foreach ($a as $value) {
			self::$TmpDir[] = $value->result;
		}
		$a=self::$TmpDir;
		return $a;
	}
	public function index( $array )
	{
		
		
		if($array)
			list($this->region_id, $this->category_id, $this->actions, $this->searches, $this->page)=$array;
		
		$GlobalConfig=array();
		$rfile = dirname(dirname(dirname($_SERVER["SCRIPT_FILENAME"])));
		
		$GlobalConfig['RegionID']=$this->region_id;
		require_once $rfile . '/lib/federal_info.lib.php';
		require_once $rfile . '/lib/sdirs.lib.php';
		require_once $rfile . '/www/classifier_'.$this->region_id.'.inc.php';
		
		self::$GlobalConfig = $GlobalConfig;
		self::$Brands = $Brands;
		self::$Dirs = $Dirs;
		self::$Classes = $Classes;
		self::$Groups = $Groups;
		
		
		if($this->searches)
		{
			$this->search = $this->searches; #XML тег search!!!! не удалять
			$search = iconv ("UTF-8",'CP1251', $this->searches);
			$this->searches = " AND (UPPER(w.ware) like \"%".strtoupper($search)."%\" or UPPER(w.FullName) like \"%".strtoupper($search)."%\")";
		}
			
		if($this->actions > 0)
			$this->category = $this->getActions();
		
		
		
		/*if($this->category_id >=0 && !$this->searches && $this->actions < 0)
		{
			$this->parentNode();
		}
		else*/if($this->category_id >=0 && ($this->searches || $this->actions > 0))
			$this->category = $this->createDirAction();
			
		if($this->category_id >=0 && !$this->searches && $this->actions < 0)
		{
			$this->createParent();
			
			if($this->category_id == 0)
				$this->category = $this->createRoot();
			else 
			{
				if($this->category_id < self::$Mult)
					$this->category = $this->createDir();
				else
				{
					
					//print $this->class_id;
					if(!$this->class_id)
						$this->category = $this->createClasses();
				}
			}
		}
		
		if($this->class_id)
			if($this->group_id || $this->actions > 0)
				$this->createProduct();
			else
				$this->createGroup();
	}
	
	
	
	protected function createRoot()
	{
		$this->displayCategoryNode( $this->parent_name );
		$wwwarez =  Models\Warez::getRootCategoryChild($this->region_id);
		$this->all_dirs($wwwarez);
		
		foreach (self::$GlobalConfig['smenu'] as $key => $value) 
		{
			$amount = 0;
			foreach ($value['dirs'] as $v) 
			{
				if(!in_array($v, $wwwarez))
					continue 2;
				
				$amount++;
				$one_key = $v;
			}
			$id = $key;
			
			if($amount == 0)
				continue;
				
			if($amount == 1 )
			{
				$key = self::ToDir($one_key);
				if($this->action_val)
					$value['name'] = self::$Dirs[$one_key];
			}
			
			
				
			$this->displayCategoryRoot($key, $value, $amount, $id);
		}
	}
	
	
	/**
	 * ф-я выводит диры в рутовой категории
	 * Enter description here ...
	 */
	protected function createDir()
	{
		$this->displayCategoryNode(ToUTF(self::$GlobalConfig['smenu'][$this->category_id]['name']));
		/*
		$q = 'SELECT distinct w.DirID as result 
			FROM warez_'.$this->region_id.' as w';
		
		if($this->searches)
			$q .= " WHERE w.warecode ".$this->searches;
			
		if($this->action_val)
			$q = 'SELECT distinct w.DirID as result 
					FROM warez_'.$this->region_id." as w
					WHERE w.warecode in (".implode(",", $this->action_val).")
					$this->searches";
					*/
		//print $q;
		$wwwarez =  Models\Warez::getRootCategoryChild($this->region_id, $this->action_val, $this->searches);
		$this->all_dirs($wwwarez);
		
		
		
		foreach (self::$GlobalConfig['smenu'][$this->category_id]['dirs'] as $value) 
		{
			$amount = 0;
			if(!in_array($value, $wwwarez))
				continue;

				
			$id = self::ToDir($value);
			/*if($this->action_val)
				$q = 'SELECT distinct w.ClassID as result, w.warecode
					FROM warez_'.$this->region_id." as w
					WHERE w.warecode in (".implode(",", $this->action_val).")
					$this->searches
					AND w.DirID = ".$value." group by result order by w.hit DESC, w.price DESC ";
			else 
				$q = 'SELECT distinct ClassID as result, w.warecode 
					FROM warez_'.$this->region_id." as w
					
					WHERE DirID = ".$value.$this->searches." group by result order by w.hit DESC, w.price DESC ";*/
				
			$wwwcat =  Models\Warez::getClassId($value, $this->region_id, $this->action_val, $this->searches);
			//print_r($wwwcat);
			//$this->all_dirs($wwwcat);
			//print_r($wwwcat);
			if($wwwcat)
				$amount = count($wwwcat);
			
			/*if($this->action_val)
				if(!in_array($value, $wwwcat))
					continue;*/
						
			if($amount == 1)
				$id = self::ToDir($value, $wwwcat[0]->result);
			
			if($amount == 0)
				continue;
				
			$this->displayCategoryDir($id, $value, $amount);
		}
		return False;
	}
	
	
	/**
	 * ф-я выводит классы в дирах
	 * Enter description here ...
	 */
	protected function createClasses()
	{
		$this->displayCategoryNode(ToUTF(self::$Dirs[$this->dir_id]));
		
		/*$q = 'SELECT distinct w.ClassID as result 
			FROM warez_'.$this->region_id." as w
			WHERE w.DirID = ".$this->dir_id;
		
		if($this->action_val)
			$q .= " AND w.warecode in (".implode(",", $this->action_val).")";
		*/
		
		if($this->searches)
			return $this->createProduct();
			
		#print $q;
		//$wwwarez =  Models\Warez::find_by_sql($q);
		$wwwarez =  Models\Warez::getClassId($this->dir_id, $this->region_id, $this->action_val);
		$this->all_dirs($wwwarez);
		//print $this->dir_id;
		//print_r(array_keys(self::$Groups[$this->dir_id]));
		foreach (array_keys(self::$Groups[$this->dir_id]) as $value) 
		{
			$amount = 0;
			if(!in_array($value, $wwwarez))
				continue;
			
			/*if($this->action_val)
				$q = 'SELECT distinct w.GrID as result, w.warecode 
					FROM warez_'.$this->region_id." as w
					WHERE w.warecode in (".implode(",", $this->action_val).")
					$this->searches
					AND w.DirID = ".$this->dir_id."
					AND w.ClassID = ".$value." group by result order by w.hit DESC, w.price DESC ";
			else 
				$q = 'SELECT distinct w.GrID as result, w.warecode 
						FROM warez_'.$this->region_id." as w
						WHERE w.DirID = ".$this->dir_id."
						$this->searches
						AND w.ClassID = ".$value." group by result order by w.hit DESC, w.price DESC ";
				
			$wwwcat =  Models\Warez::find_by_sql($q);*/
			$wwwcat =  Models\Warez::getGroupId($this->dir_id, $value, $this->region_id, $this->action_val, $this->searches);
			//print_r($wwwcat);
			//$this->all_dirs($wwwcat);
			if($wwwcat)
				$amount = count($wwwcat);
			
			/*if($this->action_val)
				if(!in_array($value, $wwwcat))
					continue;*/
			$id = self::ToDir($this->dir_id, $value);
			if($amount == 1)
			{
				/*if($this->action_val)
					$q = 'SELECT distinct w.warecode as result, w.warecode 
						FROM warez_'.$this->region_id." as w
						WHERE w.warecode in (".implode(",", $this->action_val).")
						$this->searches
						AND w.DirID = ".$this->dir_id."
						AND w.ClassID = ".$value."
						AND w.GrID = ".$wwwcat[0]->result." group by result order by w.hit DESC, w.price DESC ";
				else 
					$q = 'SELECT distinct w.warecode as result, w.warecode 
							FROM warez_'.$this->region_id." as w
							WHERE w.DirID = ".$this->dir_id."
							$this->searches
							AND w.ClassID = ".$value."
							AND w.GrID = ".$wwwcat[0]->result." group by result order by w.hit DESC, w.price DESC ";
						*/
				//print $q;
				$id = self::ToDir($this->dir_id, $value, $wwwcat[0]->result);
				//$wwwcats =  Models\Warez::find_by_sql($q);
				$wwwcat =  Models\Warez::getWaresId($this->dir_id, $value, $wwwcat[0]->result,
													$this->region_id, $this->action_val, $this->searches);
				if($wwwcat)
					$amount = count($wwwcat);
			}
			if($amount == 0)
				continue;
			//print_r($wwwcats);
			/*$category = $this->categories->addChild("category");
			$category->addChild("category_id", $id);
			$category->addChild("category_name", ToUTF(self::$Classes[$this->dir_id][$value]));
			$category->addChild("amount", $amount); 
			$icon = $category->addChild("category_icon", 
				#"http://www.mvideo.ru/Pdb/".$wwwcat[0]->warecode.".jpg"
				"http://www.mvideo.ru/mobile/public/img/".$id.".jpg"
			); 
		#"http://www.mvideo.ru/mobile/public/img/".$val->dirid."_".$val->classid."_".$val->grid.".jpg");
			$icon->addAttribute("width", "180");
			$icon->addAttribute("height", "180");*/
				$this->displayCategoryClass($id, $value, $amount);
		}
		return False;
	}
	/**
	 * функция рисует на странице информацию о категориях 
	 */
	protected function createGroup()
	{
		$this->displayCategoryNode($this->parent_name);
		
		/*$q = 'SELECT distinct w.GrID as result 
			FROM warez_'.$this->region_id." as w
			WHERE w.DirID = ".$this->dir_id."
			$this->searches
			AND w.ClassID = ".$this->class_id;*/
		$wwwarez =  Models\Warez::getGroupId($this->dir_id, $this->class_id, $this->region_id, $this->action_val, $this->searches);
		//$wwwarez =  Models\Warez::find_by_sql($q);
		$this->all_dirs($wwwarez);
		#print $this->group_id;
		//print_r(array_keys(self::$Groups[$this->dir_id]));
		foreach (array_keys(self::$Groups[$this->dir_id][$this->class_id]) as $value) 
		{
			$amount = 0;
			if(!in_array($value, $wwwarez))
				continue;
				
				
			/*if($this->action_val)
				$q = 'SELECT distinct w.warecode as result, w.warecode 
					FROM warez_'.$this->region_id." as w
					WHERE w.warecode in (".implode(",", $this->action_val).")
					$this->searches
					AND w.DirID = ".$this->dir_id."
					AND w.ClassID = ".$this->class_id."
					AND w.GrID = ".$value." group by result order by w.hit DESC, w.price DESC ";
			else 
				$q = 'SELECT distinct w.warecode as result, w.warecode 
						FROM warez_'.$this->region_id." as w
						WHERE w.DirID = ".$this->dir_id."
						$this->searches
						AND w.ClassID = ".$this->class_id."
						AND w.GrID = ".$value." group by result order by w.hit DESC, w.price ASC ";*/
				
			//$wwwcat =  Models\Warez::find_by_sql($q);
			$wwwcat =  Models\Warez::getWaresId($this->dir_id, $this->class_id, $value,
													$this->region_id, $this->action_val, $this->searches);
			//print_r($wwwcat);
			//$this->all_dirs($wwwcat);
			if($wwwcat)
				$amount = count($wwwcat);
			
			/*if($this->action_val)
				if(!in_array($value, $wwwcat))
					continue;*/
					
			if($amount == 0)
				continue;
					
			/*$category = $this->categories->addChild("category");
			$category->addChild("category_id", self::ToDir($this->dir_id, $this->class_id, $value));
			$category->addChild("category_name", ToUTF(self::$Groups[$this->dir_id][$this->class_id][$value]));
			$category->addChild("amount", $amount); 
			$icon = $category->addChild("category_icon", 
				#"http://www.mvideo.ru/Pdb/".$wwwcat[0]->warecode.".jpg" 
				"http://www.mvideo.ru/mobile/public/img/".self::ToDir($this->dir_id, $this->class_id, $value).".jpg"
			); 
		#"http://www.mvideo.ru/mobile/public/img/".$val->dirid."_".$val->classid."_".$val->grid.".jpg");
			$icon->addAttribute("width", "180");
			$icon->addAttribute("height", "180");*/
			$id = self::ToDir($this->dir_id, $this->class_id, $value);
			$this->displayCategoryGroup($id, $value, $amount);
		}
		return False;
		
		
	}
	/**
	 * функция рисует на странице информацию о продуктах в категории 
	 */
	protected function createProduct()
	{
		//print 4;
		//var_dump($this->parents);
		if($this->actions > 0 && $this->action_val)
			$this->parents->dirid .= " and w.warecode in (".implode(",", $this->action_val).") ";#$this->parents->search
		if($this->searches)
			$this->parents->dirid .= $this->searches;
		if($this->parents)
		{
			#print "ads";
			$page = $this->page;
			if($this->page > 0)
				$page = ($this->page -1)*20;
			$productes_count = 0;
			$productes_count = count(Models\Warez::getWarez($this->region_id, $this->parents, False));
			$productes_m = Models\Warez::getWarez($this->region_id, $this->parents, $page);
			//print_r($productes);
			$c_name=$this->parent_name;
			if(property_exists($this->parents, 'name'))
				$c_name = ToUTF($this->parents->name);
			
			
			//add params
			$parm = $this->params="";
			$grid=array();
			$markid = array();
			//var_dump($this->parents);
			//print "<br>";
			//$markid = array();
			
			$param_m = $this->displayProductMark();
			$param_g = $this->displayProductGroup();
			
			$this->displayProductNode($c_name);
			
			if($this->page)
				$this->displayPageNode();
				
			if($productes_m)
			{
				foreach ($productes_m as $key => $val)
				{
					if (!in_array($val->grid, $grid))
						$grid[]=$val->grid;
					if (!in_array($val->mark, $markid))
						$markid[]=$val->mark;
					//add products
					$this->displayProduct( $val );
					
				}
			}
			
			$this->displayProductMarkVal( $markid, &$param_m );
			$this->displayProductGroupVal($grid, $param_g);
			
		}
	}
	
	
	
	
	
	private function createDirAction()
	{
		$this->createParent();
		/*$q = 'SELECT distinct w.DirID as result, COUNT(w.warecode) as c 
			FROM warez_'.$this->region_id.' as w ';
		
		if($this->searches)
			$q .= " WHERE w.warecode ".$this->searches;
			
		$q .= " GROUP BY result
			ORDER BY c DESC";
		
		if($this->action_val)
			$q = 'SELECT distinct w.DirID as result, COUNT(w.warecode) as c 
					FROM warez_'.$this->region_id." as w
					WHERE w.warecode in (".implode(",", $this->action_val).")
					$this->searches
					GROUP BY result
					ORDER BY c DESC";
		
		*/
		//print "<!--\ ".$q." \-->";
		//$wwwarez =  Models\Warez::find_by_sql($q);
		//$dcg = array($this->dir_id);
		$wwwarez =  Models\Warez::getRootCategoryChild($this->region_id, $this->action_val, $this->searches);
		$res = $wwwarez;
		$this->all_dirs($wwwarez);
		#print $this->group_id;
		//print_r(array_keys(self::$Groups[$this->dir_id]));
		if($this->dir_id)
		{
			$this->parents->classid = "";
			$this->parents->grid = "";
			//var_dump($this->parents);
			if(!$this->class_id)
				return $this->createProduct();
			return false;
		}
		else 
		{
			$this->displayCategoryNode($this->parent_name);
		}
		//foreach (array_keys(self::$Dirs) as $value) 
		foreach ($res as $val) 
		{
			$value = $val->result;
			$amount = 0;
			if(in_array($value, array_keys(self::$Dirs)))
			{
				if(!in_array($value, $wwwarez))
					continue;
					
					
				/*if($this->action_val)
					$q = 'SELECT distinct w.warecode 
						FROM warez_'.$this->region_id." as w
						WHERE w.warecode in (".implode(",", $this->action_val).")
						$this->searches
						AND w.DirID = ".$value;
				else 
					$q = 'SELECT distinct w.warecode  
							FROM warez_'.$this->region_id." as w
							WHERE w.DirID = ".$value."
							$this->searches";*/
					
				$wwwcat =  Models\Warez::getWarezAction($value, $this->region_id, $this->action_val, $this->searches);
				//print_r($wwwcat);
				//$this->all_dirs($wwwcat);
				if($wwwcat)
					$amount = count($wwwcat);
				
				/*if($this->action_val)
					if(!in_array($value, $wwwcat))
						continue;*/
						
				if($amount == 0)
					continue;
						
				/*$category = $this->categories->addChild("category");
				$category->addChild("category_id", self::ToDir($value));
				$category->addChild("category_name", ToUTF(self::$Dirs[$value]));
				$category->addChild("amount", $amount); 
				$icon = $category->addChild("category_icon", 
					#"http://www.mvideo.ru/Pdb/".$wwwcat[0]->warecode.".jpg"
					"http://www.mvideo.ru/mobile/public/img/".self::ToDir($value).".jpg"
				); 
			#"http://www.mvideo.ru/mobile/public/img/".$val->dirid."_".$val->classid."_".$val->grid.".jpg");
				$icon->addAttribute("width", "180");
				$icon->addAttribute("height", "180");*/
				$id = self::ToDir($value);
				$this->displayCategoryDir($id, $value, $amount);
			}
		}
		return False;
	}
	
	/**
	 * устанавливаем ноду parent_category
	 * проверяем название и предыдущую категорию 
	 * 
	 * @param int $category_id
	 * @param object $parents_m
	 * @return array $options
	 */
	private function createParent()
	{
		/*
		 * ставим заголовки блока parents
		 * если parent = 0 то ставим список всех категорий
		 */
		$cat_parrent_name = $this->parents->parent_name = "Список категорий";
		$cat_parrent_id = $this->parents->parent_id = 0;
		//если пустой category_id
		if(!$this->category_id || $this->category_id<0 )
		{
			//$this->options = array('conditions' => "parent_id is null");
			$cat_parrent_name = $this->parent_name = "Список категорий";
			$cat_parrent_id = $this->parent_id = 0;
			$this->category_id = 0;
		}
		//если не пустой category_id
		else
		{
			//category_id меньше модификатора
			if($this->category_id < self::$Mult)
			{
				$cat_parrent_name = $this->parent_name = "Список категорий";
				$cat_parrent_id = $this->parent_id = 0;
				//$this->dir_id = $this->category_id;
				
				
			}
			else
			{
				/*
				 * проверяем parent текущей категории
				 * и выставляем id и name у родительского нода
				 */
				$this->ToClass();
				
				$this->parents->dirid = $this->dir_id;
				$this->parents->classid = $this->class_id;
				$this->parents->grid = $this->group_id;
				$this->parents->parent_name = ToUTF(self::$Dirs[$this->dir_id]);
				
				//если есть класс
				if($this->class_id)
				{
					$this->parents->parent_id = self::ToDir($this->dir_id, $this->class_id);
					$this->parents->parent_name = ToUTF(self::$Classes[$this->dir_id][$this->class_id]);
					$cat_parrent_id = self::ToDir($this->dir_id);
					$cat_parrent_name = ToUTF(self::$Dirs[$this->dir_id]);
					//если еть группа
					if($this->group_id)
					{
						$this->parents->parent_id = self::ToDir($this->dir_id, $this->class_id, $this->group_id);
						$this->parents->parent_name = ToUTF(self::$Groups[$this->dir_id][$this->class_id][$this->group_id]);
						$cat_parrent_id = self::ToDir($this->dir_id, $this->class_id);
						$cat_parrent_name = ToUTF(self::$Classes[$this->dir_id][$this->class_id]);
					}
				}
				//если нет класса проверяем родительские категории
				else 
				{
					foreach (self::$GlobalConfig['smenu'] as $key => $value) 
					{
						if(in_array($this->parents->dirid, $value['dirs']))
							{
								$cat_parrent_id = $this->parents->parent_id = ToUTF($key);
								$cat_parrent_name = $this->parents->parent_name = ToUTF($value['name']);
								break;
							}
					}
				}
				
				$this->parent_name = $this->parents->parent_name;
				$this->parent_id = $this->parents->parent_id;
			}
		}
		
		$this->displayCategoryParentNode($cat_parrent_id, $cat_parrent_name);
		return false;//$this->options;
	}
	
	private function getActions()
	{
		switch ((int)$this->actions)
		{
			case 1:
				$action = 6;
				break;
			case 3:
				$action = 0;
				return $this->getActionFederal();
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
			return $this->displayCategoryAction( $act );
	}
	/**
	 * текущая федеральная акция
	 */
	private function getActionFederal()
	{
		$time = time();
		
		//$keys = array_keys(self::$GlobalConfig['fed_act']);
		foreach (self::$GlobalConfig['fed_act'] as $key => $val) 
		{
			
			if($key < $time)
			{
				if($val['end_date']>=$time)
				{
					//print 1;
					$url = str_replace("/", "", $val['link']);//link
					$imgfile = "imgs/action/main/$url.jpg";
					$this->action = "";
					$this->displayActionImage($imgfile);
					
					$this->action->addChild("description", ToUTF($val['name']));//descr
					
					$this->action->addChild("url", "http://www.mvideo.ru/".$url."-cond/");
					$this->action->addChild("link", "http://www.mvideo.ru/".$url."/");
					//print $url;
					return $this->putActions($url);
				}
			}
		}
		
	}
	/**
	 * собирает товары участвующие в акции
	 * в массив $this->action_val
	 * @param unknown_type $name
	 */
	private function getActionsVal($name)
	{
		
		$options = array('select' => 'w.warecode',
						'from' => 'segment_cache sc',
						'joins'=>" join warez_$this->region_id w on (sc.warecode=w.warecode)",
						'conditions' =>"sc.region_id=$this->region_id and sc.segment_name='$name' ");
		//print $this->searches;
		if($this->searches)
			$options['conditions'] .= $this->searches;
		$segment = Models\Segments::find('all', $options);
		foreach ($segment as $val)
		{
			$this->action_val[] = $val->warecode;
		}
		return False;
	}
	
	private function getProductMarkVal( $markid, &$param )
	{
		foreach($markid as $val)
		{
			$group = Models\Marks::find('first', array("markid"=>$val));
			if($group)
			{
				$this->displayProductGroupOption(&$param, StripTags($group->markname), $val);
			}
		}
	}
	
	private function getProductGroupVal( $grid, &$param )
	{
		foreach($grid as $val)
		{
			$group = Models\Groups::find('first', array("grid"=>$val));
			if($group)
			{
				$this->displayProductGroupOption(&$param, ToUTF($group->grname), $val);
			}
		}
	}
	
	private function displayCategoryAction( $act )
	{
		$url = str_replace("/", "", $act->segment_name);//link
		$imgfile = "imgs/action/header_$url.jpg";
		
		$this->action = "";
		$this->displayActionImage($imgfile);
		
		$this->action->addChild("description", ToUTF($act->segment_info));
		$this->action->addChild("url", "http://www.mvideo.ru/".str_replace("_", "-", $act->segment_name)
															."/?ref=left_bat_". $act->segment_name);
		$this->action->addChild("link", "http://www.mvideo.ru/".$url."/");
		$categorys = $this->getActionsVal($act->segment_name);
		return $categorys;
	}
	
	private function displayCategoryRoot($key, $value, $amount, $id)
	{
			$category = $this->categories->addChild("category");
			$category->addChild("category_id", $key);
			$category->addChild("category_name", ToUTF($value['name']));
			$this->displayCategotyIcon($category, $id, $amount);
	}
	
	private function displayCategoryDir($id, $value, $amount)
	{
		$name = ToUTF(self::$Dirs[$value]);
		$this->displayCategorySection($name, $id, $amount);
	}
	
	private function displayCategoryClass($id, $value, $amount)
	{
		$name = ToUTF(self::$Classes[$this->dir_id][$value]);
		$this->displayCategorySection($name, $id, $amount);
	}
	
	private function displayCategoryGroup($id, $value, $amount)
	{
		$name = ToUTF(self::$Groups[$this->dir_id][$this->class_id][$value]);
		$this->displayCategorySection($name, $id, $amount);
	}
	
	private function displayCategorySection($name, $id, $amount)
	{
		$category = $this->categories->addChild("category");
		$category->addChild("category_id", $id);
		$category->addChild("category_name", $name);
		$this->displayCategotyIcon($category, $id, $amount);
	}
	
	private function displayCategotyIcon(&$category, $id, $amount)
	{
		$category->addChild("amount", $amount); 
		$icon = $category->addChild("category_icon", 
			#"http://www.mvideo.ru/Pdb/".$wwwcat[0]->warecode.".jpg"
			"http://www.mvideo.ru/mobile/public/img/".$id.".jpg"
		); 
		#"http://www.mvideo.ru/mobile/public/img/".$val->dirid."_".$val->classid."_".$val->grid.".jpg");
		$icon->addAttribute("width", "180");
		$icon->addAttribute("height", "180");
	}
	
	
	
	
	
	
	
	
	private function displayCategoryNode( $name )
	{
		$this->categories="";
		$this->categories->addAttribute("category_id", $this->category_id);
		$this->categories->addAttribute("category_name", $name );
	}
	
	private function displayCategoryParentNode($cat_parrent_id, $cat_parrent_name)
	{
		$this->parent_category="";
		$this->parent_category->addChild("category_id", $cat_parrent_id);
		$this->parent_category->addChild("category_name", $cat_parrent_name);
	}
	
	private function displayProduct( $val )
	{
		$product = $this->products->addChild("product");
		$product->addChild("product_id", ToUTF($val->warecode));
		$product->addChild("title", StripTags($val->name));
		$val->getDesctiptions();
		$product->addChild("description", StripTags($val->description));
		//$rewiews = Models\Reviews::first(array('select' => 'count(rating) c, sum(rating) s', 'conditions' => array('warecode = ?', $val->warecode)));
		$val->getRatingRev();
		$product->addChild("rating", $val->rating);
		$product->addChild("reviews_num", $val->reviews);
		$product->addChild("inet_price", $val->inetprice);
		
		$dic = $val->getInetDiscountStatus($val->warecode, $this->region_id);
		$product->addChild("card_discount", $dic);
		
		if($val->oldprice)
			$old_price = $val->oldprice;
		else
			$old_price = $val->price;
			
		$product->addChild("old_price", $old_price);
		
		$product->addChild("price", $val->price);
		$image = $product->addChild("image", "http://www.mvideo.ru/Pdb/$val->warecode.jpg"); 
		$image->addAttribute("width", "180");
		$image->addAttribute("height", "180");
	}
	
	private function displayProductNode( $name )
	{
		$this->products="";
		$this->products->addAttribute("category_id", $this->category_id);
		$this->products->addAttribute("category_name", $name);
	}
	
	private function displayProductMark()
	{
		$param = $this->params->addChild("param"); 
		$param->addAttribute("param_name", "mark");
		$param->addAttribute("title", "Производители");
		$param->addAttribute("current_value", "0");
		$option = $param->addChild("option", "Все производители");
		$option->addAttribute("value", "0");
		return $param;
	}
	
	
	private function displayProductGroup()
	{
		$param = $this->params->addChild("param"); 
		$param->addAttribute("param_name", "grid");
		$param->addAttribute("title", "Группы");
		$param->addAttribute("current_value", "0");
		$option = $param->addChild("option", "Все группы");
		$option->addAttribute("value", "0");
		
		return $param;
	}
	
	
	
	private function displayProductGroupOption(&$param, $name, $val)
	{
		$option = $param->addChild("option", ToUTF($group->grname));
		$option->addAttribute("value", $val);
	}
	
	private function displayPageNode()
	{
		$this->pages="";
		$this->pages->addChild("amount", $productes_count);
		$this->pages->addChild("onpage", "20");
		$this->pages->addChild("page", $this->page);
	}
	/**
	 * создает ноду с картинкой для акции
	 * @param unknown_type $img
	 */
	private function displayActionImage($img)
	{
		$imgdir = dirname(dirname($_SERVER["SCRIPT_FILENAME"]));
		
		$fimgs = "http://www.mvideo.ru/$img";
		//print $imgdir."/".$imgfile;
		if(file_exists($imgdir."/".$img))
		{
			$imgsize = getimagesize($imgdir."/".$img);
		
			//создаем картинку
			$images = $this->action->addChild("image", $fimgs);
			
			//задаем размеры
			$images->addAttribute("width", $imgsize[0]);
			$images->addAttribute("height", $imgsize[1]);
		}
	}
	
	
	protected static function ToDir($d, $c = 0, $g = 0)
	{
		$d = $d*self::$Mult;
		$c = $c*self::$MultC;
		$g = $g*self::$MultG;
		return $d+$c+$g;
	}
	
	protected function ToClass()
	{
		$this->dir_id = floor($this->category_id / self::$Mult);
		$this->class_id = floor(($this->category_id % self::$Mult) / self::$MultC);
		$this->group_id = floor((($this->category_id % self::$Mult) % self::$MultC) / self::$MultG);
	}
}