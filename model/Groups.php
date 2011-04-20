<?php
/**
  * аблица групп товаров
  * 
  * @package    Groups
  * @subpackage ActiveRecord
  * @since      19.04.2011 10:56:49
  * @author     enesterov
  * @category   models
  */

	namespace Models;
	use ActiveRecord;

class Groups extends ActiveRecord\Model
{
	static $table_name = 'groups';
	static $primary_key = 'grid';
	static $connection = CONNECTION;
}