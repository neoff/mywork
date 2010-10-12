<?php
/**
 * модель БД регионы 
 *
 * @package    model
 * @subpackage Regions
 * @since      07.10.2010 10:20:00
 * @author     enesterov
 * @category   model
 */

	namespace Models;
	use ActiveRecord;
	
class Regions extends ActiveRecord\Model
{
	static $table_name = 'regions';
	static $primary_key = 'region_id';
	static $connection = CONNECTION;
	//static $db = 'test';
}