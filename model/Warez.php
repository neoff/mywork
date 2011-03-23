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
		'name' => 'fullname', 
		'price'=>'discounted',
		'oldprice'=>'oldprice',
		'inetprice'=>'inetdiscounted'
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
	
	public static function getWarez($region_id, $parents, $page = False)
	{
		//print $page;
		//var_dump($parents);
		//var_dump(isset($parents->asdasdasdasd));
		$limit="";
		if($page!==False)
			$limit = " limit 20 offset $page";
		$sql_impl="";
		
		if(isset($parents->dirid))
		{
			$sql_impl.='w.DirID = ';
			
			$subject = $parents->dirid;
			$pattern = '/^\d+/';
			preg_match($pattern, $subject,$pp);
			if(empty($pp))
				$sql_impl.='0';
				
			$sql_impl .= $parents->dirid;
		}
		if(isset($parents->classid))
			if($parents->classid)
				$sql_impl.="  and w.ClassID = ". $parents->classid;
			
		if(isset($parents->grid))
			if($parents->grid)
				$sql_impl.=" and w.GrID = " .$parents->grid;
		
		/*if($parents->dirid && $parents->search)
			$sql_impl.= " and ";
			
		if($parents->search)
			$sql_impl.= $parents->search;*/
		if(!$region_id || !$sql_impl)
			return;
		$sql = 'select w.* from `warez_' .$region_id . '` as w 
				where ' . $sql_impl." order by price ASC ". $limit;
		//print $sql_impl;
		#print $sql;
		return self::find_by_sql($sql);
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
						'conditions' => array('approved=1 and warecode = ?', $this->warecode));
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
	
	public function getInetDiscountStatus($ware, $region)
	{
		if( $region!=1 )
			return 0;
			
		$q = "SELECT segments.online_stop  
				FROM segment_cache
				JOIN segments ON segments.segment_name = segment_cache.segment_name
				WHERE segment_cache.region_id = ".$region." AND segment_cache.warecode = ".$ware;
		$res = self::find_by_sql($q);;
		if(!empty($res))
			if($res[0]->online_stop > 0)
				return 0;
			return 1;
			
		return 1;
		
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
	
	public static function getAccess($region_id, $code, $limit){
		//select * from linkw inner join warez_1 on (linkw.warecodel = warez_1.warecode) where warecodem = 11032149;
		$join = "inner join warez_$region_id w on (warecodel = w.warecode) where warecodem = $code";
		$sel = array('select' => '*','joins'=> $join, 'order' => 'w.grid asc');
		if($limit===true)
			$sel['group']='w.grid';
		//var_dump($sel);
		return self::all($sel);
		
		
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
						'conditions' => array('approved=1 and warecode = ?', $this->warecode));
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
