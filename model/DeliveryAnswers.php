<?php
/**
  * 
  * 
  * @package    DeliveryAnswers
  * @subpackage 
  * @since      05.05.2011 8:48:23
  * @author     enesterov
  * @category   controller
  */

	namespace Models;
	use ActiveRecord;

class DeliveryAnswers extends ActiveRecord\Model
{
	static $table_name = 'delivery_answers';
	static $primary_key = 'id';
	static $connection = CONNECTION;
}