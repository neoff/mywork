<?php
/**  
 * 
 * 
 * @package    model
 * @subpackage Warez
 * @since      11.10.2010 12:13:09
 * @author     enesterov
 * @category   models
 */

	namespace Models;
	use ActiveRecord;

class Warez extends ActiveRecord\Model
{
	private $where;
	static $finder=array();
	static $table_name = 'warez_1';
	static $primary_key = 'category_id';
	static $connection = CONNECTION;
	
	public static function sql($region_id, $where="", $array=array())
	{
		return self::find_by_sql('select * from warez_' .$region_id . ' ' . $where, $array);
	}
	public function settable($name)
	{
		$this->table_name = $name;
	}
	public function SetParam($region_id, $array)
	{
		$this->where = " where ";
		foreach($array as $key => $value) {
			list($mod, $key) = explode("_", $key);
			$this->where .=" $key=? ".$mod;
			$this->finder[]=$value;
		}
		return array($this->where,$this->finder);
	}
}

class Warereviews extends ActiveRecord\Model
{
	static $table_name = 'warereviews';
	static $connection = CONNECTION;
}
