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
	public $description;
	public $rating = 0.0;
	public $reviews = 0;
	static $alias_attribute = array(
		'name' => 'ware', 
		'small_price'=>'inetprice'
		);
	
	
	public static function sql($region_id, $where="", $array=array())
	{
		return self::find_by_sql('select * from warez_' .$region_id . ' ' . $where, $array);
	}

	public function SetParam($region_id, $array)
	{
		$where = " where ";
		foreach($array as $key => $value) {
			list($mod, $key) = explode("_", $key);
			$where .=" $key=? ".$mod;
			$this->finder[]=$value;
		}
		return array($where,$this->finder);
	}
	
	public static function getWarez($region_id, $parents, $page = "0")
	{
		if($page===False)
			return count(self::find_by_sql('select * from `warez_' .$region_id . '` 
				where DirID = '.$parents->dirid ." and ClassID = " 
				. $parents->classid ." and GrID = " .$parents->grid));
				
		return self::find_by_sql('select * from `warez_' .$region_id . '` 
				where DirID = '.$parents->dirid ." and ClassID = " 
				. $parents->classid ." and GrID = " .$parents->grid. " limit 20 offset $page");
	}
	
	public function getDesctiptions()
	{
		$description = Description::first(array("id"=>$this->warecode));
		if($description)
			if($description->text)
				$this->description = $description->text;
	}
	
	public function getRatingRev()
	{
		$options = array('select' => 'count(rating) c, sum(rating) s', 
						'conditions' => array('warecode = ?', $this->warecode));
		$rewiews = Reviews::first($options);
		if($options)
		{
			$this->reviews = 0;
			$this->rating = number_format(0, 1, '.', '');
			if($rewiews->c > 0)
			{
				$this->rating = round((float)((int)$rewiews->s/(int)$rewiews->c),1, PHP_ROUND_HALF_UP);
				$this->reviews = $rewiews->c;
			}
		}
	}
	
	public static function getWarezAction($region_id, $array, $condition = "")
	{
		if($array)
		{
			return self::find_by_sql('select c.* from categories c left join warez_' .
							$region_id . " w on (c.DirID=w.DirID and c.ClassID=w.ClassID and c.GrID=w.GrID ) 
							where w.warecode in (".implode(",", $array).") $condition group BY c.category_id ");
		}
		else
			return $array;
		
	}
	
	

}

class Link extends ActiveRecord\Model{
	static $table_name = 'warez_aks';
	//static $belongs_to = array(array('warez_1'));
	static $connection = CONNECTION;
	public $description;
	public $rating = 0.0;
	public $reviews = 0;
	
	public static function getAccess($region_id, $code){
		//select * from linkw inner join warez_1 on (linkw.warecodel = warez_1.warecode) where warecodem = 11032149;
		$join = "inner join warez_$region_id w on (warecodel = w.warecode) where warecodem = $code";
		return self::all(array('select' => '*','joins'=> $join, 'order' => 'w.grid asc'));
		
		
	}
	
	public function getDesctiptions()
	{
		$description = Description::first(array("id"=>$this->warecode));
		if($description)
			if($description->text)
				$this->description = $description->text;
	}
	
	public function getRatingRev()
	{
		$options = array('select' => 'count(rating) c, sum(rating) s', 
						'conditions' => array('warecode = ?', $this->warecode));
		$rewiews = Reviews::first($options);
		if($options)
		{
			$this->reviews = 0;
			$this->rating = number_format(0, 1, '.', '');
			if($rewiews->c > 0)
			{
				$this->rating = number_format(round((float)((int)$rewiews->s/(int)$rewiews->c),1, PHP_ROUND_HALF_UP), 1, '.', '');
				$this->reviews = $rewiews->c;
			}
		}
	}
	
}
