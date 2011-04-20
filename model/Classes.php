<?php
/**
  * таблица класов товаров
  * 
  * @package    Class
  * @subpackage ActiveRecord
  * @since      19.04.2011 10:56:01
  * @author     enesterov
  * @category   models
  */

	namespace Models;
	use ActiveRecord;
	
class Classes extends ActiveRecord\Model
{
	static $table_name = 'classes';
	static $primary_key = 'classid';
	static $connection = CONNECTION;
}