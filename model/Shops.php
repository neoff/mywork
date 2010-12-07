<?php
/**  
 *  магазины в регионах
 * 
 * @package    model
 * @subpackage Shops
 * @since      11.10.2010 11:13:26
 * @author     enesterov
 * @category   models
 */

	namespace Models;
	use ActiveRecord;
	
class Shops extends ActiveRecord\Model
{
	static $table_name = 'shops';
	static $primary_key = 'id';
	static $connection = CONNECTION;
	
	#поля в таблице
	static $id = "id";
	static $shop_id = "shop_id";
	static $name = "shop_name";
	static $metro = "metro";
	static $address = "address";
	static $day_hours = "day_hours";
	static $holyday_hours = "holyday_hours";
	static $phone = "phone";
	public $longitude = "";#explode(",", $val->map_latlng);
	public $latitude = "";
	static $howto = "howto";
	static $map_zoom = "map_zoom";
	static $p = "p";
	
	public function coordinates(){
		if($this->map_latlng)
		{
			$coord = explode(",", $this->map_latlng);
			if(count($coord)==2) list($this->longitude, $this->latitude) = array($coord[0], $coord[1]);
		}
	}
}