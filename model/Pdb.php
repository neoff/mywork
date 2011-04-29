<?php
/**
  * 
  * 
  * @package    Pdb_ext.php
  * @subpackage 
  * @since      29.04.2011 9:46:19
  * @author     enesterov
  * @category   controller
  */

	namespace Models;
	use ActiveRecord;

class Pdb extends ActiveRecord\Model
{
	static $table_name = 'Pdb_ext';
	static $connection = CONNECTION;
}