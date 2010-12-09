<?php
/**  
 * 
 * 
 * @package    model
 * @subpackage Category
 * @since      11.10.2010 12:13:09
 * @author     enesterov
 * @category   models
 */

	namespace Models;
	use ActiveRecord;

class Category extends ActiveRecord\Model
{
	static $table_name = 'categories';
	static $primary_key = 'category_id';
	static $connection = CONNECTION;
	
	public static function getWarezAction($region_id, $array, $condition = "")
	{
		if($array)
		{
			//print implode(",", $array);
			$join = "left join warez_$region_id w on (c.DirID=w.DirID )";
			$options = array('select'=> 'c.*', 
							'from' => 'categories as c', 
							'joins' => $join, 
							'group' => 'category_id',
							'conditions' => "w.warecode in (".implode(",", $array).") $condition");
			return self::find('all', $options);
			
		}
		else
			return $array;
		
	}
	
	public static function findByNameCategory($region_id, $search, $category = false)
	{
		
		if((int)$category > 0)
		{
			$category = " and c.category_id = $category";
		}
		else
			$category = "";
			
		$join = "left join warez_$region_id w on (c.DirID=w.DirID )";
		$options = array('select'=> 'c.*', 
						'from' => 'categories as c', 
						'joins' => $join, 
						'group' => 'c.category_id',
						'conditions' => "(w.ware like \"%$search%\" or w.FullName like \"%$search%\") $category");
		return self::find('all', $options);
	}
}

class Marks extends ActiveRecord\Model
{
	static $table_name = 'marks';
	static $primary_key = 'markid';
	static $connection = CONNECTION;
	
}

class Groups extends ActiveRecord\Model
{
	static $table_name = 'groups';
	static $primary_key = 'grid';
	static $connection = CONNECTION;
	
}