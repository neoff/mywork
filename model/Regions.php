<?php
/**
 * модель БД регионы 
 *
 * @package    model
 * @subpackage Regions
 * @since      07.10.2010 10:20:00
 * @author     enesterov
 * @category   models
 */

	namespace Models;
	use ActiveRecord;
	
class Regions extends ActiveRecord\Model
{
	static $table_name = 'regions';
	static $primary_key = 'region_id';
	static $connection = CONNECTION;
	
	#поля в таблице
	static $alias_attribute = array(
		'id' => 'region_id',
		'name' => 'region_name',
		'virtual' => 'is_virtual'
		);
	public $longitude = "";#explode(",", $val->map_latlng);
	public $latitude = "";
	
	public function coordinates(){
		if($this->center)
		{
			$coord = explode(",", $this->center);
			if(count($coord)==2) list($this->longitude, $this->latitude) = array($coord[0], $coord[1]);
		}
	}
}