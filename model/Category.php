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
	
	
	public static function warez($region_id, $parrents)
	{
		return self::find_by_sql('select * from `warez_' .$region_id . '` 
				where DirID = '.$parrents->dirid ." and ClassID = " 
				. $parrents->classid ." and GrID = " .$parrents->grid );
	}
	
}