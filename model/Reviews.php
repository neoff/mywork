<?php
/**  
 * 
 * 
 * @package    model
 * @subpackage Reviews
 * @since      14.10.2010 14:10:40
 * @author     enesterov
 * @category   models
 */

	namespace Models;
	use ActiveRecord;

class Reviews extends ActiveRecord\Model
{
	static $table_name = 'reviews_new';
	static $connection = CONNECTION;
}