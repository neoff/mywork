<?php
/**
  * таблица reclama
  * 
  * @package    Reklama
  * @subpackage ActiveRecord
  * @since      03.05.2011 13:45:37
  * @author     enesterov
  * @category   models
  */

	namespace Models;
	use ActiveRecord;

class Reklama extends ActiveRecord\Model
{
	static $table_name = 'reklama';
	static $connection = CONNECTION;
	
	
}
