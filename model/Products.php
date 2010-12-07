<?php
/**  
 * 
 * 
 * @package    model
 * @subpackage Product
 * @since      11.10.2010 15:57:26
 * @author     enesterov
 * @category   models
 */

	namespace Models;
	use ActiveRecord;
	

	
class Products extends ActiveRecord\Model
{
	static $table_name = 'descriptionlist';
	static $connection = CONNECTION;
}
