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
			if(count($coord)==2) list($this->latitude,$this->longitude ) = array($coord[0], $coord[1]);
		}
	}
	
	private static function getOptions()
	{
		$join = 'INNER JOIN shops s ON s.shop_id=a.shop_id';
		$option = array(
					'select' => 'IF(LENGTH(metro),1,0) AS in_city, a.shop_id, s.*, a.amount AS InShopQty',
					'from' => 'amounts a',
					'joins' => $join,
					'order' => 'in_city desc, metro,shop_spool');
		
		return $option;
	}
	
	public static function getPickup($region, $inetprice)
	{
		$pickup = 0;
		if(Warez::getBigPrice($inetprice))
		{
			if($inetprice->hit != 2 || $inetprice->hit != 3)
			{
				$option = self::getOptions();
				$option['conditions'] = array('warecode= ? AND amount>= ? AND s.pickup= ? AND s.publication= ? AND region_id= ?',
												$inetprice->warecode, 
												3, 
												1, 
												1, 
												$region);
				$reg = self::first($option);
				if($reg)
				{
						$pickup = 1;
				}
			}
		}
		return $pickup;
	}
	
	public static function getPickupShops($region, $inetprice)
	{
		if(Warez::getBigPrice($inetprice))
		{
			if(!in_array($inetprice->hit, array(2,3)))
			{
				$option = self::getOptions();
				$option['conditions'] = array('warecode= ? AND amount>= ? AND s.pickup= ? AND amount>=3 AND region_id= ?',
												$inetprice->warecode, 
												3, 
												1, 
												$region);
				return self::find('all', $option);
			}
			return false;
		}
		return false;
	}
	
	public static function getAllShops($region)
	{
		$options = array('region_id' => $region, "publication" => 1);
		return self::find('all', $options);
	}
}