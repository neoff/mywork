<?php
/**
  * таблица links
  * 
  * @package    Links.php
  * @subpackage ActiveRecord
  * @since      19.04.2011 10:49:14
  * @author     enesterov
  * @category   models
  */

	namespace Models;
	use ActiveRecord;
	
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