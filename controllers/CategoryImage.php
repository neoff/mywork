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


	/*namespace Controllers;
	use Models;*/
	ini_set('display_errors', True);
	error_reporting(E_ALL);
	date_default_timezone_set( 'Europe/Moscow' );
	define( "ROOT_PATH", dirname(dirname(__FILE__)) );
	
	print ROOT_PATH;
	define("FILE", ROOT_PATH . "/config.ini");
	require_once ROOT_PATH . '/conf_parse.php';
	
	$conn = new config(FILE);
	define( "DEBUG", ($conn->debug)?$conn->debug:True );
	define( 'CONNECTION', ($conn->base)?$conn->base:'develop' );
	$_SERVER['HTTP_HOST'] = "localhost";
	
	require_once ROOT_PATH . '/lib/ActiveRecord.php';
	require_once ROOT_PATH . '/lib/Template.php';
	require_once ROOT_PATH . '/config/config.php';
	require_once ROOT_PATH . '/model/__init__.php';
	require_once ROOT_PATH . '/controllers/Category.php';
	
class ControllerCategoryImage extends Controllers\ControllerCategory{
	private $region_id;
	
	public function image()
	{
		$this->region_id = "1";
		if($this->rootCategories());
			/*if($this->Dirs())
				if($this->Classes())
					$this->Groups();*/
	}
	
	private function getFile($file, $warecode)
	{
		$file = ROOT_PATH . "/public/img/$file.jpg";
		$idir = "ln -s ../../Pdb/$warecode.jpg " . $file;
		print $idir;
		if(!file_exists($file))
			exec($idir);
		
	}
	private function ToDir($d, $c = 0, $g = 0)
	{
		$d = $d*self::$Mult;
		$c = $c*self::$MultC;
		$g = $g*self::$MultG;
		return $d+$c+$g;
	}
	private function ToClass()
	{
		$this->dir_id = floor($this->category_id / self::$Mult);
		$this->class_id = floor(($this->category_id % self::$Mult) / self::$MultC);
		$this->group_id = floor((($this->category_id % self::$Mult) % self::$MultC) / self::$MultG);
	}
	private function rootCategories()
	{
		$q = 'SELECT distinct w.DirID as result 
				FROM warez_'.$this->region_id." as w";
		
		print $q;
		$wwwarez =  Models\Warez::find_by_sql($q);
		
		foreach (self::$GlobalConfig['smenu'] as $key => $value) 
		{
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
			
				
			#/public/img/s$id.jpg"
			
		}
		//exit();
		return True;
		
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
			"http://www.mvideo.ru/Pdb/".$wwwcat[0]->warecode.".jpg"); #TODO откуда брать иконку категории???
			$icon->addAttribute("width", "180");
			$icon->addAttribute("height", "180");
		}
		return True;
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
			"http://www.mvideo.ru/Pdb/".$wwwcat[0]->warecode.".jpg"
				#"http://www.mvideo.ru/mobile/public/img/".$this->dir_id."_".$value."_.jpg"
			); 
		#"http://www.mvideo.ru/mobile/public/img/".$val->dirid."_".$val->classid."_".$val->grid.".jpg");
			$icon->addAttribute("width", "180");
			$icon->addAttribute("height", "180");
		}
		return True;
	}
	/**
	 * функция рисует на странице информацию о категориях 
	 */
	private function Groups()
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
				"http://www.mvideo.ru/Pdb/".$wwwcat[0]->warecode.".jpg" 
				#"http://www.mvideo.ru/mobile/public/img/".$this->dir_id."_".$this->class_id."_".$value.".jpg"
			); 
		#"http://www.mvideo.ru/mobile/public/img/".$val->dirid."_".$val->classid."_".$val->grid.".jpg");
			$icon->addAttribute("width", "180");
			$icon->addAttribute("height", "180");
		}
		return True;
		
		
	}
	
}

$run = new ControllerCategoryImage();
$run->image();