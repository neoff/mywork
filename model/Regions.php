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

class Regions extends ActiveRecord\Model
{
	static $table_name = 'regions';
	static $primary_key = 'id';
	static $connection = CONNECTION;
	static $db = 'test';
}