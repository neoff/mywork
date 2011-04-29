<?php
/**
  * 
  * 
  * @package    Ikupons.php
  * @subpackage 
  * @since      29.04.2011 11:17:52
  * @author     enesterov
  * @category   controller
  */

	namespace Models;
	use ActiveRecord;

class Ikupons extends ActiveRecord\Model
{
	static $table_name = 'ikupons';
	static $connection = CONNECTION;
	
	public static function getKupon($region_id, $mark_id, $dir_id, $class_id, $group_id)
	{
		/*$query = "
					SELECT DISTINCT ik.*, ik.ikupon_warecode as warecode
					FROM ikupons ik
					LEFT JOIN ikupons_regions ikr ON ik.ikupon_warecode=ikr.ikupon_warecode
					LEFT JOIN ikupons_marks ikm ON ik.ikupon_warecode=ikm.ikupon_warecode
					LEFT JOIN ikupons_cats ikc ON ik.ikupon_warecode=ikc.ikupon_warecode
					WHERE (ikr.region_id=".$GlobalConfig['RegionID']."  OR ikr.ikupon_warecode IS NULL)
					AND (ikm.MarkID=".$ware['mark']." OR ikm.ikupon_warecode IS NULL) 
					AND (ikc.DirID=".$ware['DirID']." OR ikc.DirID IS NULL) 
					AND (ikc.ClassID=".$ware['ClassID']." OR ikc.ClassID IS NULL OR ikc.ClassID=0) 
					AND (ikc.GrID=".$ware['GrID']." OR ikc.GrID IS NULL OR ikc.GrID=0)
			  ";*/
		$join = "LEFT JOIN ikupons_regions ikr ON ikupons.ikupon_warecode=ikr.ikupon_warecode
				LEFT JOIN ikupons_marks ikm ON ikupons.ikupon_warecode=ikm.ikupon_warecode
				LEFT JOIN ikupons_cats ikc ON ikupons.ikupon_warecode=ikc.ikupon_warecode";
		
		$options = array('select' => "DISTINCT ikupons.*, ikupons.ikupon_warecode as warecode",
						'joins' => $join,
						'conditions' => array(
										"(ikr.region_id= ?  OR ikr.ikupon_warecode IS NULL)
										AND (ikm.MarkID= ? OR ikm.ikupon_warecode IS NULL) 
										AND (ikc.DirID= ? OR ikc.DirID IS NULL) 
										AND (ikc.ClassID= ? OR ikc.ClassID IS NULL OR ikc.ClassID=0) 
										AND (ikc.GrID= ? OR ikc.GrID IS NULL OR ikc.GrID=0)", 
										$region_id, 
										$mark_id,
										$dir_id,
										$class_id,
										$group_id
											));
		return self::find('all', $options);
	}
}