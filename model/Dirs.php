<?php
/**
  * таблица разделов товаров
  * 
  * @package    Dirs
  * @subpackage ActiveRecord
  * @since      19.04.2011 10:55:51
  * @author     enesterov
  * @category   models
  */

	namespace Models;
	use ActiveRecord;
	
class Dirs extends ActiveRecord\Model
{
	static $table_name = 'dirs';
	static $primary_key = 'dirid';
	static $connection = CONNECTION;
}