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
}