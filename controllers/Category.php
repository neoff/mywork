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
	public $search;
	
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
			$this->searches = " AND (w.ware like \"%$search%\" or w.FullName like \"%$search%\")";
		}
			
		if($this->actions > 0)
			$this->category = $this->action();
		
		
		
		if($this->category_id >=0 && !$this->searches && $this->actions < 0)
		{
			$this->parent_node();
		}
		elseif($this->category_id >=0 && ($this->searches || $this->actions > 0))
			$this->category = $this->ActionDirs();
			
		if($this->category_id >=0 && !$this->searches && $this->actions < 0)
			if($this->category_id == 0)
				$this->category = $this->rootCategories();
			//if($this->category_id >0)
			//	$this->category = Models\Category::find('all', $this->options);
			else 
			{
				if($this->category_id < self::$Mult)
					$this->category = $this->Dirs();
				else
				{
					
					//print $this->class_id;
					if(!$this->class_id)
						$this->category = $this->Classes();
				}
			}
		//}
		
			
			
		//print_r($this->category);
		//$condition = "";
		//$categoryssss = Models\Category::getWarezAction($this->region_id, $this->action_val, $condition);
		//print_r($categoryssss);
		if($this->class_id)
			if($this->group_id || $this->actions > 0)
				$this->productes();
			else
				$this->categories();
	}
	
	private function rootCategories()
	{
		
		
		$this->categories="";
		$this->categories->addAttribute("category_id", $this->category_id);
		$this->categories->addAttribute("category_name", $this->parent_name);
		
		
		if($this->action_val)
		{
			$q = 'SELECT distinct w.DirID as result 
				FROM warez_'.$this->region_id." as w
				WHERE w.warecode in (".implode(",", $this->action_val).")
				$this->searches ";
			//print $q;
			$actWarez =  Models\Warez::find_by_sql($q);
			$this->all_dirs($actWarez);
			#print_r($actWarez);
			
		}
		
		$q = 'SELECT distinct w.DirID as result 
				FROM warez_'.$this->region_id." as w";
		
		if($this->searches)
			$q .= " WHERE w.warecode ".$this->searches;
		
		$wwwarez =  Models\Warez::find_by_sql($q);
		$this->all_dirs($wwwarez);
		
		foreach (self::$GlobalConfig['smenu'] as $key => $value) 
		{
			//$am = Models\Category::find('all', array('conditions' => 
			//						array('parent_id is null and dirid in (?)', $value['dirs'])
			//						));
			$amount = 0;
			foreach ($value['dirs'] as $v) 
			{
				if(!in_array($v, $wwwarez))
					continue 2;
				
				if($this->action_val)
					if(!in_array($v, $actWarez))
						continue;
				
				
				$amount++;
				$one_key = $v;
				#print $key."-".$v."-".$amount."-".$one_key."\n";
				
			}
			#print "--".$key."-".$amount."-".$one_key."\n";
			#print "------".$key."---\n";
			//$amount = count(self::$Groups[$v]);
			$id = $key;
			if($amount == 1 )
			{
				//$val = $am[0];
				//$amount =  $this->recurseAmount($val);
				$key = $this->ToDir($one_key);
				if($this->action_val)
					$value['name'] = self::$Dirs[$one_key];
				//$value['name'] = $val->name;
			}
			if($amount == 0)
				continue;
			#print "------".$key."---\n";
			//if($amount == 0 )
			//	continue;
			$category = $this->categories->addChild("category");
			$category->addChild("category_id", $key);
			$category->addChild("category_name", ToUTF($value['name']));
			$category->addChild("amount", $amount); 
			$icon = $category->addChild("category_icon", "http://www.mvideo.ru/mobile/public/img/s$id.jpg"); 
			$icon->addAttribute("width", "180");
			$icon->addAttribute("height", "180");
		}
		//exit();
		return False;
		
	}
	protected function ToDir($d, $c = 0, $g = 0)
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
	/**
	 * ф-я выводит диры в рутовой категории
	 * Enter description here ...
	 */
	private function Dirs()
	{
		
		$this->categories="";
		$this->categories->addAttribute("category_id", $this->category_id);
		$this->categories->addAttribute("category_name", ToUTF(self::$GlobalConfig['smenu'][$this->category_id]['name']));
		
		
		$q = 'SELECT distinct w.DirID as result 
			FROM warez_'.$this->region_id.' as w';
		
		if($this->searches)
			$q .= " WHERE w.warecode ".$this->searches;
			
		if($this->action_val)
			$q = 'SELECT distinct w.DirID as result 
					FROM warez_'.$this->region_id." as w
					WHERE w.warecode in (".implode(",", $this->action_val).")
					$this->searches";
					
		//print $q;
		$wwwarez =  Models\Warez::find_by_sql($q);
		$this->all_dirs($wwwarez);
		
		
		
		foreach (self::$GlobalConfig['smenu'][$this->category_id]['dirs'] as $value) 
		{
			$amount = 0;
			if(!in_array($value, $wwwarez))
				continue;

				
			$id = $this->ToDir($value);
			if($this->action_val)
				$q = 'SELECT distinct w.ClassID as result, w.warecode
					FROM warez_'.$this->region_id." as w
					WHERE w.warecode in (".implode(",", $this->action_val).")
					$this->searches
					AND w.DirID = ".$value." group by result order by w.hit DESC, w.price DESC ";
			else 
				$q = 'SELECT distinct ClassID as result, w.warecode 
					FROM warez_'.$this->region_id." as w
					
					WHERE DirID = ".$value.$this->searches." group by result order by w.hit DESC, w.price DESC ";
				
			$wwwcat =  Models\Warez::find_by_sql($q);
			//print_r($wwwcat);
			//$this->all_dirs($wwwcat);
			//print_r($wwwcat);
			if($wwwcat)
				$amount = count($wwwcat);
			
			/*if($this->action_val)
				if(!in_array($value, $wwwcat))
					continue;*/
						
			if($amount == 1)
				$id = $this->ToDir($value, $wwwcat[0]->result);
			
			if($amount == 0)
				continue;
				
			$category = $this->categories->addChild("category");
			$category->addChild("category_id", $id);
			$category->addChild("category_name", ToUTF(self::$Dirs[$value]));
			$category->addChild("amount", $amount); 
			$icon = $category->addChild("category_icon", 
			#"http://www.mvideo.ru/Pdb/".$wwwcat[0]->warecode.".jpg"
			"http://www.mvideo.ru/mobile/public/img/$id.jpg"
			); 
			$icon->addAttribute("width", "180");
			$icon->addAttribute("height", "180");
		}
		return False;
	}
	private function ActionDirs()
	{
		$this->parent_node();
		
		
		
		
		
		$q = 'SELECT distinct w.DirID as result, COUNT(w.warecode) as c 
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
		
		
		//print "<!--\ ".$q." \-->";
		$wwwarez =  Models\Warez::find_by_sql($q);
		$res = $wwwarez;
		$this->all_dirs($wwwarez);
		#print $this->group_id;
		//print_r(array_keys(self::$Groups[$this->dir_id]));
		if($this->dir_id)
		{
			$this->parents->classid = "";
			$this->parents->grid = "";
			//var_dump($this->parents);
			return $this->productes();
		}
		else 
		{
			$this->categories="";
			$this->categories->addAttribute("category_id", $this->category_id);
			$this->categories->addAttribute("category_name", $this->parent_name);
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
					
					
				if($this->action_val)
					$q = 'SELECT distinct w.warecode 
						FROM warez_'.$this->region_id." as w
						WHERE w.warecode in (".implode(",", $this->action_val).")
						$this->searches
						AND w.DirID = ".$value;
				else 
					$q = 'SELECT distinct w.warecode  
							FROM warez_'.$this->region_id." as w
							WHERE w.DirID = ".$value."
							$this->searches";
					
				$wwwcat =  Models\Warez::find_by_sql($q);
				//print_r($wwwcat);
				//$this->all_dirs($wwwcat);
				if($wwwcat)
					$amount = count($wwwcat);
				
				/*if($this->action_val)
					if(!in_array($value, $wwwcat))
						continue;*/
						
				if($amount == 0)
					continue;
						
				$category = $this->categories->addChild("category");
				$category->addChild("category_id", $this->ToDir($value));
				$category->addChild("category_name", ToUTF(self::$Dirs[$value]));
				$category->addChild("amount", $amount); 
				$icon = $category->addChild("category_icon", 
					#"http://www.mvideo.ru/Pdb/".$wwwcat[0]->warecode.".jpg"
					"http://www.mvideo.ru/mobile/public/img/".$this->ToDir($value).".jpg"
				); 
			#"http://www.mvideo.ru/mobile/public/img/".$val->dirid."_".$val->classid."_".$val->grid.".jpg");
				$icon->addAttribute("width", "180");
				$icon->addAttribute("height", "180");
			}
		}
		return False;
	}
	/**
	 * ф-я выводит классы в дирах
	 * Enter description here ...
	 */
	private function Classes()
	{
		#print $this->category_id;
		$this->categories="";
		$this->categories->addAttribute("category_id", $this->category_id);
		$this->categories->addAttribute("category_name", ToUTF(self::$Dirs[$this->dir_id]));
		
		$q = 'SELECT distinct w.ClassID as result 
			FROM warez_'.$this->region_id." as w
			WHERE w.DirID = ".$this->dir_id;
		
		if($this->action_val)
			$q .= " AND w.warecode in (".implode(",", $this->action_val).")";
			
		if($this->searches)
			return $this->productes();
			
		#print $q;
		$wwwarez =  Models\Warez::find_by_sql($q);
		$this->all_dirs($wwwarez);
		//print $this->dir_id;
		//print_r(array_keys(self::$Groups[$this->dir_id]));
		foreach (array_keys(self::$Groups[$this->dir_id]) as $value) 
		{
			$amount = 0;
			if(!in_array($value, $wwwarez))
				continue;
			
			if($this->action_val)
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
				
			$wwwcat =  Models\Warez::find_by_sql($q);
			//print_r($wwwcat);
			//$this->all_dirs($wwwcat);
			if($wwwcat)
				$amount = count($wwwcat);
			
			/*if($this->action_val)
				if(!in_array($value, $wwwcat))
					continue;*/
			$id = $this->ToDir($this->dir_id, $value);
			if($amount == 1)
			{
				if($this->action_val)
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
						
				//print $q;
				$id = $this->ToDir($this->dir_id, $value, $wwwcat[0]->result);
				$wwwcats =  Models\Warez::find_by_sql($q);
				if($wwwcat)
					$amount = count($wwwcats);
			}
			if($amount == 0)
				continue;
			//print_r($wwwcats);
			$category = $this->categories->addChild("category");
			$category->addChild("category_id", $id);
			$category->addChild("category_name", ToUTF(self::$Classes[$this->dir_id][$value]));
			$category->addChild("amount", $amount); 
			$icon = $category->addChild("category_icon", 
				#"http://www.mvideo.ru/Pdb/".$wwwcat[0]->warecode.".jpg"
				"http://www.mvideo.ru/mobile/public/img/".$id.".jpg"
			); 
		#"http://www.mvideo.ru/mobile/public/img/".$val->dirid."_".$val->classid."_".$val->grid.".jpg");
			$icon->addAttribute("width", "180");
			$icon->addAttribute("height", "180");
		}
		return False;
	}
	/**
	 * функция рисует на странице информацию о категориях 
	 */
	private function categories()
	{
		
		$this->categories="";
		$this->categories->addAttribute("category_id", $this->category_id);
		$this->categories->addAttribute("category_name", $this->parent_name);
		
		$q = 'SELECT distinct w.GrID as result 
			FROM warez_'.$this->region_id." as w
			WHERE w.DirID = ".$this->dir_id."
			$this->searches
			AND w.ClassID = ".$this->class_id;
		
		$wwwarez =  Models\Warez::find_by_sql($q);
		$this->all_dirs($wwwarez);
		#print $this->group_id;
		//print_r(array_keys(self::$Groups[$this->dir_id]));
		foreach (array_keys(self::$Groups[$this->dir_id][$this->class_id]) as $value) 
		{
			$amount = 0;
			if(!in_array($value, $wwwarez))
				continue;
				
				
			if($this->action_val)
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
						AND w.GrID = ".$value." group by result order by w.hit DESC, w.price ASC ";
				
			$wwwcat =  Models\Warez::find_by_sql($q);
			//print_r($wwwcat);
			//$this->all_dirs($wwwcat);
			if($wwwcat)
				$amount = count($wwwcat);
			
			/*if($this->action_val)
				if(!in_array($value, $wwwcat))
					continue;*/
					
			if($amount == 0)
				continue;
					
			$category = $this->categories->addChild("category");
			$category->addChild("category_id", $this->ToDir($this->dir_id, $this->class_id, $value));
			$category->addChild("category_name", ToUTF(self::$Groups[$this->dir_id][$this->class_id][$value]));
			$category->addChild("amount", $amount); 
			$icon = $category->addChild("category_icon", 
				#"http://www.mvideo.ru/Pdb/".$wwwcat[0]->warecode.".jpg" 
				"http://www.mvideo.ru/mobile/public/img/".$this->ToDir($this->dir_id, $this->class_id, $value).".jpg"
			); 
		#"http://www.mvideo.ru/mobile/public/img/".$val->dirid."_".$val->classid."_".$val->grid.".jpg");
			$icon->addAttribute("width", "180");
			$icon->addAttribute("height", "180");
		}
		return False;
		
		
	}
	/**
	 * функция рисует на странице информацию о продуктах в категории 
	 */
	private function productes()
	{
		
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
			if($productes_m)
				foreach ($productes_m as $key => $val)
				{
					if (!in_array($val->grid, $grid))
						$grid[]=$val->grid;
					if (!in_array($val->mark, $markid))
						$markid[]=$val->mark;
					//add products
	
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
					$product->addChild("cardDiscount", $dic);
					
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
					$option_m = $param_m->addChild("option", StripTags($m_marks->markname));
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
		$cat_parrent_name = $this->parents->parent_name = "Список категорий";
		$cat_parrent_id = $this->parents->parent_id = 0;
		if(!$this->category_id || $this->category_id<0 )
		{
			//$this->options = array('conditions' => "parent_id is null");
			$cat_parrent_name = $this->parent_name = "Список категорий";
			$cat_parrent_id = $this->parent_id = 0;
			$this->category_id = 0;
		}
		elseif($this->category_id<self::$Mult)
		{
			//$cond = array('conditions' => "parent_id is null");
			$cat_parrent_name = $this->parent_name = "Список категорий";
			$cat_parrent_id = $this->parent_id = 0;
			
		}
		else
		{
			/**
			 * проверяем parent текущей категории
			 * и выставляем id и name у родительского нода
			 */
			$this->ToClass();
			
			$this->parents->dirid = $this->dir_id;
			$this->parents->classid = $this->class_id;
			$this->parents->grid = $this->group_id;
			$this->parents->parent_name = ToUTF(self::$Dirs[$this->dir_id]);
			
			if($this->class_id)
			{
				$this->parents->parent_id = $this->ToDir($this->dir_id, $this->class_id);
				$this->parents->parent_name = ToUTF(self::$Classes[$this->dir_id][$this->class_id]);
				$cat_parrent_id = $this->ToDir($this->dir_id);
				$cat_parrent_name = ToUTF(self::$Dirs[$this->dir_id]);
				if($this->group_id)
				{
					$this->parents->parent_id = $this->ToDir($this->dir_id, $this->class_id, $this->group_id);
					$this->parents->parent_name = ToUTF(self::$Groups[$this->dir_id][$this->class_id][$this->group_id]);
					$cat_parrent_id = $this->ToDir($this->dir_id, $this->class_id);
					$cat_parrent_name = ToUTF(self::$Classes[$this->dir_id][$this->class_id]);
				}
			}
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
		$this->parent_category="";
		$this->parent_category->addChild("category_id", $cat_parrent_id);
		$this->parent_category->addChild("category_name", $cat_parrent_name);
		
		return $this->options;
	}
	
	private function search()
	{
		/*$this->search = $this->searches; #XML тег search!!!! не удалять
		$search = $this->searches;
		
		//if($search[0]!="%")
		//{
			//print $search;exit();
			//$search = preg_replace('/%([[:alnum:]]{2})/i', '&#x\1;',$search);
			//$search = html_entity_decode($search,null,'UTF-8');
			$search=iconv ("UTF-8",'CP1251', $search );*/
		//}
			
		//$catid = " and c.parent_id is null ";
		//$catid = " and c.parent_id is null ";
		//if($this->parents)
			//if($this->parents->grid)
		//		$catid .= " and c.parent_id is not null and c.category_id = " . $this->category_id;
		//if($this->category_id>0)		return array();
		
		//$category = Models\Category::findByNameCategory($this->region_id, $search, $catid);
		//print $catid;
		//var_dump($this->parents);
		//print_r($category);
		//exit();
		//if(!$category)
		//{
			
			//$this->products($region_id, $category_id, $parents, $array);
		//	return array();
		//}
		//else
		//{
		//	if(count($category)==1)
		//	{
		//		return array();
		//	}
		//}
		//return $category;
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
			$this->action->addChild("link", "http://www.mvideo.ru/".$url."/");
			$categorys = $this->putActions($act->segment_name);
			return $categorys;
		}
	}
	
	/**
	 * собирает товары участвующие в акции
	 * в массив $this->action_val
	 * @param unknown_type $name
	 */
	private function putActions($name)
	{
		
		$options = array('select' => 'w.warecode',
						'from' => 'segment_cache sc',
						'joins'=>" join warez_$this->region_id w on (sc.warecode=w.warecode)",
						'conditions' =>"sc.region_id=$this->region_id and sc.segment_name='$name' ");
		//print $this->searches;
		if($this->searches)
			$options['conditions'] .= $this->searches;# AND"
		/*{
			$search = $this->searches;
			//if($search[0]!="%")
			//{
				
				//$search = preg_replace('/%([[:alnum:]]{2})/i', '&#x\1;',$search);
				//$search = html_entity_decode($search,null,'UTF-8');
				$search=iconv ("UTF-8",'CP1251', $search );
			//}
			// $options['joins'] = "";
			$options['conditions'] = $options['conditions'].
					" and (w.ware like \"%$search%\" or w.FullName like \"%$search%\")";
		}*/
		//print_r($options);
		$segment = Models\Segments::find('all', $options);
		#$segment = Models\Segments::segmentDirs($this->region_id, $name);
		//var_dump($segment);
		#exit();
		foreach ($segment as $val)
		{
			$this->action_val[] = $val->warecode;
		}
		//print $region_id. $array;
		
		/*if(!$this->category_id)
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
		}*/
		//print_r($this->action_val);
		#if(!$this->searches)
		#	$this->parent_node();
		return False;
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
					//print 1;
					$url = str_replace("/", "", $val['link']);//link
					$imgfile = "imgs/action/main/$url.jpg";
					$this->action = "";
					$this->actionImage($imgfile);
					
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
	 * ф-я вычисляет колличество подкатегорий в категории
	 */
	/*private function amount($val)
	{
		if($val)
		{
			#var_dump($val);
			#print $val->category_id;
			$amount = Models\Category::count(array('conditions' => "parent_id = $val->category_id"));
			
			if(!$amount) 
			{
				$ids = new SetId($val->dirid, $val->classid, $val->grid);
				$amount = count(Models\Warez::getWarez($this->region_id, $ids));
			}
			if($this->actions > 0)
			{
				$amount = Models\Warez::find_by_sql('select count(1) as amount, ware, DirID from `warez_' .$this->region_id . '` 
								where warecode in ('.implode(",", $this->action_val).') and DirID = '.$val->dirid.' group by DirID'  );
				
				//print_r($amount);
				$count = 0;
				foreach($amount as $v)
				{
					$count = $v->amount;
				}
				$amount = $count;
				
			}
			return $amount;
		}
	}
	
	private function recurseAmount($val)
	{
		$amount = $this->amount($val);
		if($this->actions > 0)
			return $amount;
		if($amount == 1 )
		{
			$val = Models\Category::find('first',array('parent_id' => $val->category_id));
			$amount = $this->recurseAmount($val);
		}
		
		if($amount > 1 )
		{
			#print $val->category_id . " - id----cat - ".$val->name." ".$amount." \n";
			$vCount = Models\Category::find('all',array('parent_id' => $val->category_id));
			if($vCount)
			{
				$cc = 0;
				foreach($vCount as $vk=>$vc)
				{
					#print ToUTF($vc->name)." - ".$vc->category_id." pod_category\n";
					$cnt = $this->amount($vc);
					#print $val->category_id." + ".$vc->category_id." + ".$vc->name." + ".$cnt." count + ".$cc." -pod_category\n";
					if($cnt == 1 )
					{
						$vcc = Models\Category::find('first',array('parent_id' => $vc->category_id));
						$cnt = $this->amount($vc);
						#print $cnt." count if one ----------------------\n";
					}
					if($amount > 1 )
					{
						$cnt = $this->recurseAmount($vc);
					}
					if($cnt == 0 )
						continue;
					$cc++;
					#print $val->category_id." - ".$vc->category_id." - ".$vc->name." - ".$cnt." - ".$cc." count after check\n";
					#print $cc." --- final amount --- \n";
				}
				$amount = $cc;
			}
		}
		return $amount;
	}
	*/
}