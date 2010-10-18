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
	
	public static function getWarez($region_id, $parrents)
	{
		return self::find_by_sql('select * from `warez_' .$region_id . '` 
				where DirID = '.$parrents->dirid ." and ClassID = " 
				. $parrents->classid ." and GrID = " .$parrents->grid );
	}
	
	public static function getWarezAction($region_id, $array, $condition = "")
	{
		return self::find_by_sql('select c.* from categories c left join warez_' .
							$region_id . " w on (c.DirID=w.DirID and c.ClassID=w.ClassID and c.GrID=w.GrID ) 
							where w.warecode in (".implode(",", $array).") 
							$condition
							group BY c.category_id ");
	}
	
	
}

class Link extends ActiveRecord\Model{
	static $table_name = 'warez_aks';
	//static $belongs_to = array(array('warez_1'));
	static $connection = CONNECTION;
	
	public static function getAccess($region_id, $code){
		//select * from linkw inner join warez_1 on (linkw.warecodel = warez_1.warecode) where warecodem = 11032149;
		$join = "inner join warez_$region_id on (linkw.warecodel = warez_$region_id.warecode) where warecodem = $code";
		return self::all(array('select' => '*','joins'=> $join));
		
		
	}
	
}
