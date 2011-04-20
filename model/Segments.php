<?php
/**
  * таблица сегментов
  * 
  * @package    Segments
  * @subpackage ActiveRecord
  * @since      19.04.2011 10:53:23
  * @author     enesterov
  * @category   models
  */

	namespace Models;
	use ActiveRecord;
	
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
		print $sql;
		return self::find_by_sql($sql);
	}
}