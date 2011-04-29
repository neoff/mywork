<?php
/**  
 * таблица продуктов
 * 
 * @package    Warez
 * @subpackage ActiveRecords
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
	
	/**
	 * выбираем варез из БД согласно параметров
	 * @param int $region_id
	 * @param obj $parents
	 * @param int|bool $page
	 */
	public static function getWarez($region_id, $parents, $page = False)
	{
		if($page > 0)
			$page = ($page -1)*20;
				
		$limit="";
		if($page!=False)
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
		
		if(!$region_id || !$sql_impl)
			return;
		
		$sql = 'select w.* from `warez_' .$region_id . '` as w 
				where ' . $sql_impl." order by price ASC ". $limit;
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
	
	public static function getBigPrice($inetprice)
	{
		if((int)$inetprice->inetprice > 3000)
			return true;
		return false;
	}
	
	public static function getWarezAction($dir, $region_id = 1, $action = "", $search = "")
	{
		$sql = 'SELECT distinct w.warecode 
						FROM warez_'.$region_id." as w
						WHERE w.DirID = ".$dir;
		if($action)
			$sql .= " AND w.warecode in (".implode(",", $action).") ";
		
		if($search)
				$sql .= $search;
		
		return self::find_by_sql($sql);
	}
	
	public static function getRootCategoryChild($region_id = 1, $action = "", $search = "", $dcg = array())
	{
		//print $sql;
		$dir = $class = $group = 0;
		if( $dcg )
		{
			$count = count($dcg);
			if($count < 3)
			{
				$d = array_fill($count+1, 3-$count, 0);
				$dcg = array_merge($dcg, $d);
			}
			list($dir, $class, $group) = $dcg;
		}
		
		$sql = 'SELECT distinct w.DirID as result, COUNT(w.warecode) as c 
				FROM warez_'.$region_id." as w";
		
		$groups = " GROUP BY result ";
		if($search || $action)
		{
			$groups .= " ORDER BY c DESC ";
			$sql .= " WHERE ";
				
			
			
			if($search && !$action)
				$sql .= " w.warecode ";
				
			if($action)
				$sql .= " w.warecode in (".implode(",", $action).") ";
			
			if($search)
				$sql .= $search;
		}
		
		$sql .= $groups;
		return self::find_by_sql($sql);
	}
	
	public static function getCertificate($region_id = 1, $warecode, $price)
	{
		
		$join = 'JOIN linkw ON warez.warecode=linkw.warecodem 
				JOIN certificati ON certificati.certwarecode=linkw.warecodel
				JOIN scprices USING (certwarecode)';
		/*$sql = "SELECT
				certificati.certwarecode AS warecode,
				certificati.ware,
				MAX(warepricefrom) warepricefrom,
				MAX(CertPrice) certprice
				FROM warez_$region_id AS warez
				JOIN linkw ON warez.warecode=linkw.warecodem
				JOIN certificati ON certificati.certwarecode=linkw.warecodel
				JOIN scprices USING (certwarecode)
				WHERE warepricefrom<=$price
				AND warez.warecode=$warecode
				GROUP BY certificati.certwarecode";*/
		
		
		
		$sql = array(
			'select' => "certificati.certwarecode AS warecode,
						CONCAT(certificati.ware,' на ',warez.FullName) AS FullName,
						certificati.ware,
						MAX(warepricefrom) warepricefrom,
						MAX(CertPrice) certprice",
			'from' => 'warez_'.$region_id.' as warez',
			'joins' => $join,
			'conditions'=>array('warepricefrom<= ? AND warez.warecode= ? ', $price, $warecode),
			'group' => 'certificati.certwarecode');
		
		return self::find('all', $sql);
	}
	
	public static function getReleted($region_id, $val)
	{
		$join = "JOIN Pdb USING(warecode) 
				LEFT JOIN sw ON warez.warecode=sw.warecode 
				LEFT JOIN marks ON warez.mark=marks.MarkID,dirs,classes,groups ";
		$pmin = 0.85 * $val->price;
		$pmax   = 1.15 * $val->price;
		$sql = array(
			'select' => "DISTINCT warez.*, 
						0 as end_for_cnt, 
						0 as is_cnt_down, 
						(Discounted > DC) AS Discountable,
						IF(warez.InetQty,1,0) AS Presence,
						sw.pkarta pkarta, 
						dirs.DirName AS DirName, 
						classes.ClassName AS ClassName, 
						groups.GrName AS GrName, 
						marks.MarkName AS MarkName",
			'from' => 'warez_'.$region_id.' as warez',
			'joins' => $join,
			'group' => 'warez.warecode', 
			'order' => 'warez.spool, warez.GrID, warez.Discounted ASC',
			'limit' => 5,
			'conditions'=>array('warez.Show 
								AND warez.DirID= ?
								AND warez.ClassID= ?
								AND warez.GrID= ?
								AND warez.GrID!=486 
								AND warez.warecode!= ?
								AND warez.Discounted>=? 
								AND warez.Discounted<=? 
								AND warez.InetQty > 0 
								AND warez.ShopsQty > 0 
								AND warez.DirID=dirs.DirID 
								AND warez.ClassID=classes.ClassID 
								AND warez.GrID=groups.GrID ', 
								$val->dirid, 
								$val->classid,
								$val->grid,
								$val->warecode,
								$pmin,
								$pmax
								)
		);
		/*SELECT DISTINCT warez.*, 0 as end_for_cnt, 0 as is_cnt_down, (Discounted > DC) AS Discountable,IF(warez.InetQty,1,0) 
		 AS Presence,sw.pkarta pkarta, dirs.DirName AS DirName, classes.ClassName AS ClassName, groups.GrName AS GrName, marks.MarkName AS MarkName 
FROM warez_1 AS warez 
JOIN Pdb USING(warecode) 
LEFT JOIN sw ON warez.warecode=sw.warecode 
LEFT JOIN marks ON warez.mark=marks.MarkID,dirs,classes,groups 
WHERE warez.Show 
AND warez.DirID=11 
AND warez.ClassID=12 
AND warez.GrID=1029 
AND warez.GrID!=486 
AND warez.warecode!=30012771 
AND warez.Discounted>=671 
AND warez.Discounted<=908 
AND warez.InetQty > 0 
AND warez.ShopsQty > 0 
AND warez.DirID=dirs.DirID 
AND warez.ClassID=classes.ClassID 
AND warez.GrID=groups.GrID 
GROUP BY warez.warecode 
ORDER BY warez.spool, warez.GrID, warez.Discounted ASC LIMIT 5*/
		return self::find('all', $sql);
	}
	/************* deprecated *************************/
	public static function getClassId($dir, $region_id = 1, $action = "", $search = "")
	{
		$sql = 'SELECT distinct ClassID as result, w.warecode 
				FROM warez_'.$region_id." as w
				WHERE w.DirID = ".$dir;
		
		if($action)
			$sql .= " AND w.warecode in (".implode(",", $action).") ";
		
		if($search)
			$sql .= $search;
			
		$sql .= " GROUP BY result ORDER BY w.hit DESC, w.price DESC ";
		
		return self::find_by_sql($sql);
	}
	
	public static function getGroupId($dir, $classid, $region_id = 1, $action = "", $search = "")
	{
		$sql = 'SELECT distinct w.GrID as result, w.warecode 
				FROM warez_'.$region_id." as w
				WHERE w.DirID = ".$dir." AND w.ClassID = ".$classid;
		
		if($action)
			$sql .= " AND w.warecode in (".implode(",", $action).") ";
		
		if($search)
			$sql .= $search;
			
		$sql .= " GROUP BY result ORDER BY w.hit DESC, w.price DESC ";
		
		return self::find_by_sql($sql);
	}
	
	public static function getWaresId($dir, $classid, $groupid, $region_id = 1, $action = "", $search = "")
	{
		$sql = 'SELECT distinct w.warecode as result, w.warecode 
				FROM warez_'.$region_id." as w
				WHERE w.DirID = ".$dir." AND w.ClassID = ".$classid." AND w.GrID = ".$groupid;
		
		if($action)
			$sql .= " AND w.warecode in (".implode(",", $action).") ";
		
		if($search)
			$sql .= $search;
			
		$sql .= " GROUP BY result ORDER BY w.hit DESC, w.price DESC ";
		
		return self::find_by_sql($sql);
	}

}


