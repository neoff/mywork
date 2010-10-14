<?php
/**  
 * 
 * 
 * @package    model
 * @subpackage Desclist
 * @since      13.10.2010 17:58:37
 * @author     enesterov
 * @category   none
 */

	namespace Models;
	use ActiveRecord;

class Description extends ActiveRecord\Model
{
	static $table_name = 'warereviews';
	static $connection = CONNECTION;
}

class Oprionlist extends ActiveRecord\Model
{
	static $table_name = 'descriptionlist';
	static $connection = CONNECTION;
}