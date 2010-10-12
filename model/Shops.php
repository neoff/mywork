<?php
/**  
 * 
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
	//static $db = 'test';
}