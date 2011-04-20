<?php
/**  
 * таблица описания товаров
 * 
 * @package    model
 * @subpackage ActiveRecord
 * @since      13.10.2010 17:58:37
 * @author     enesterov
 * @category   model
 */

	namespace Models;
	use ActiveRecord;

class Description extends ActiveRecord\Model
{
	static $table_name = 'warereviews';
	static $connection = CONNECTION;
	static $alias_attribute = array(
		'id' => 'warecode',
		'text' => 'reviewtext'
		);
	public $description;
	
	
	static function desctriptions()
	{
		if($this->reviewtext)
			$this->description = $this->reviewtext;
	}
}

class Optionlist extends ActiveRecord\Model
{
	static $table_name = 'descriptionlist';
	static $connection = CONNECTION;
}