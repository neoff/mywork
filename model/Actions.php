<?php
/**  
 * 
 * 
 * @package    model
 * @subpackage Actions
 * @since      11.10.2010 12:13:09
 * @author     enesterov
 * @category   models
 */

	namespace Models;
	use ActiveRecord;

class Actions extends ActiveRecord\Model
{
	static $table_name = 'segments';
	static $primary_key = 'segment_id';
	static $connection = CONNECTION;
}

class Segments extends ActiveRecord\Model
{
	static $table_name = 'segment_cache';
	//static $primary_key = 'segment_id';
	static $connection = CONNECTION;
	
	public function segmentWarez($options)
	{
		return self::find('all', $options);
	}
	
	public function segmentDirs($region, $name)
	{
		$sql = "
			SELECT t.*,DirName
			FROM dirs
			JOIN (
				SELECT
					w.DirID,
					COUNT(1) AS num
				FROM segment_cache
				JOIN warez_".$region." AS w ON w.warecode = segment_cache.warecode
				WHERE segment_cache.segment_name = '".$name."'
				AND segment_cache.region_id = ".$region."
	            GROUP BY w.DirID
			) AS t ON t.DirID=dirs.DirID
			ORDER BY t.num DESC
		";
		return self::find_by_sql($sql);
	}
}