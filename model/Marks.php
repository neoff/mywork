<?php
/**
  * таблица производителей
  * 
  * @package    Marks
  * @subpackage ActiveRecord
  * @since      19.04.2011 10:59:47
  * @author     enesterov
  * @category   models
  */

	namespace Models;
	use ActiveRecord;
	
class Marks extends ActiveRecord\Model
{
	static $table_name = 'marks';
	static $primary_key = 'markid';
	static $connection = CONNECTION;
	
}