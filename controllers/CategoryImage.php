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
	$_SERVER["SCRIPT_FILENAME"] = FILE;
	
	require_once ROOT_PATH . '/lib/ActiveRecord.php';
	require_once ROOT_PATH . '/lib/Template.php';
	require_once ROOT_PATH . '/config/config.php';
	require_once ROOT_PATH . '/model/__init__.php';
	require_once ROOT_PATH . '/controllers/Category.php';
	
class ControllerCategoryImage extends Controllers\ControllerCategory{
	
	public function image()
	{
		$this->region_id = "1";
		$this->index(array());
		if($this->rootCategories());
			
				/*if()
					$this->Groups();*/
	}
	
	private function getFile($file, $warecode)
	{
		$file = ROOT_PATH . "/public/img/$file.jpg";
		$imgdir = dirname(ROOT_PATH);
		$idir = "ln -s $imgdir/Pdb/$warecode.jpg " . $file . "\n";
		print $idir;
		if(!file_exists($file))
			exec($idir);
		
	}
	private function rootCategories()
	{
		$q = 'SELECT distinct w.DirID as result, w.warecode 
				FROM warez_'.$this->region_id." as w
				GROUP BY w.DirID";
		
		//print $q;
		$wwwarez =  Models\Warez::find_by_sql($q);
		$this->all_dirs($wwwarez);
		//var_dump($wwwarez);
		foreach (self::$GlobalConfig['smenu'] as $key => $value) 
		{
			$amount = 0;
			foreach ($value['dirs'] as $v) 
			{
				//array_keys($wwwarez, "blue")
				if(!in_array($v, $wwwarez))
					continue 2;
				
				
				if($amount!=0)
					break;
				$wdir =  Models\Warez::first(array('conditions'=>"dirid = ". $v, 'order' => 'price ASC'));
				$this->getFile("s".$key, $wdir->warecode);
				$amount = $v;
			}
			
			$this->category_id = $key;
			$this->Dirs();
		}
		return True;
	}
	
	/**
	 * ф-я выводит диры в рутовой категории
	 * Enter description here ...
	 */
	private function Dirs()
	{
		
		
		$q = 'SELECT distinct w.DirID as result 
			FROM warez_'.$this->region_id.' as w';
		
		
		//print $q;
		$wwwarez =  Models\Warez::find_by_sql($q);
		$this->all_dirs($wwwarez);
		
		
		
		foreach (self::$GlobalConfig['smenu'][$this->category_id]['dirs'] as $value) 
		{
			$amount = 0;
			if(!in_array($value, $wwwarez))
				continue;
			
			$this->dir_id = $value;
			$id = $this->ToDir($value);
			$q = 'SELECT distinct ClassID as result, w.warecode 
					FROM warez_'.$this->region_id." as w
					
					WHERE DirID = ".$value." group by result order by w.hit DESC, w.price DESC ";
				
			$wwwcat =  Models\Warez::find_by_sql($q);
			if(!$wwwcat)
				continue;
			$this->getFile($id, $wwwcat[0]->warecode);
			$this->Classes();
		}
		return True;
	}
	/**
	 * ф-я выводит классы в дирах
	 * Enter description here ...
	 */
	private function Classes()
	{
		$q = 'SELECT distinct w.ClassID as result 
			FROM warez_'.$this->region_id." as w
			WHERE w.DirID = ".$this->dir_id;
		
		$wwwarez =  Models\Warez::find_by_sql($q);
		$this->all_dirs($wwwarez);
		//print $this->dir_id;
		//print_r(array_keys(self::$Groups[$this->dir_id]));
		foreach (array_keys(self::$Groups[$this->dir_id]) as $value) 
		{
			$amount = 0;
			if(!in_array($value, $wwwarez))
				continue;
			
			$q = 'SELECT distinct w.GrID as result, w.warecode 
						FROM warez_'.$this->region_id." as w
						WHERE w.DirID = ".$this->dir_id."
						AND w.ClassID = ".$value." group by result order by w.hit DESC, w.price DESC ";
				
			$wwwcat =  Models\Warez::find_by_sql($q);
			if(!$wwwcat)
				continue;
			$this->class_id = $value;
			$id = $this->ToDir($this->dir_id, $value);
			$this->getFile($id, $wwwcat[0]->warecode);
			$this->Groups();
			#"http://www.mvideo.ru/mobile/public/img/".$this->dir_id."_".$value."_.jpg"
		
		}
		return True;
	}
	/**
	 * функция рисует на странице информацию о категориях 
	 */
	private function Groups()
	{
		$q = 'SELECT distinct w.GrID as result 
			FROM warez_'.$this->region_id." as w
			WHERE w.DirID = ".$this->dir_id."
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
				
				
			$q = 'SELECT distinct w.warecode as result, w.warecode 
						FROM warez_'.$this->region_id." as w
						WHERE w.DirID = ".$this->dir_id."
						AND w.ClassID = ".$this->class_id."
						AND w.GrID = ".$value." group by result order by w.hit DESC, w.price ASC ";
				
			$wwwcat =  Models\Warez::find_by_sql($q);
			if(!$wwwcat)
				continue;
			//print_r($wwwcat);
			//$this->all_dirs($wwwcat);
			$id = $this->ToDir($this->dir_id, $this->class_id, $value);
			$this->getFile($id, $wwwcat[0]->warecode);
		}
		return True;
		
		
	}
	public function __destruct()
	{
		return False;
	}
	
}

$run = new ControllerCategoryImage();
$run->image();