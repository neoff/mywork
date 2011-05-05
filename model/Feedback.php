<?php
/**
  * таблица отзывово о покупке
  * 
  * @package    Feedback
  * @subpackage ActiveRecord
  * @since      05.05.2011 8:47:57
  * @author     enesterov
  * @category   models
  */

	namespace Models;
	use ActiveRecord;

class Feedback extends ActiveRecord\Model
{
	static $table_name = 'feedback';
	static $primary_key = 'id';
	static $connection = CONNECTION;
}