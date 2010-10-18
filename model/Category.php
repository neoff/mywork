<?php
/**  
 * 
 * 
 * @package    model
 * @subpackage Category
 * @since      11.10.2010 12:13:09
 * @author     enesterov
 * @category   models
 */

	namespace Models;
	use ActiveRecord;

class Category extends ActiveRecord\Model
{
	static $table_name = 'categories';
	static $primary_key = 'category_id';
	static $connection = CONNECTION;
	
}

class Marks extends ActiveRecord\Model
{
	static $table_name = 'marks';
	static $primary_key = 'markid';
	static $connection = CONNECTION;
	
}

class Groups extends ActiveRecord\Model
{
	static $table_name = 'groups';
	static $primary_key = 'grid';
	static $connection = CONNECTION;
	
}