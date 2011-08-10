<?php
/**
  * 
  * 
  * @package    Amounts.php
  * @subpackage 
  * @since      28.04.2011 12:30:29
  * @author     enesterov
  * @category   controller
  */

	namespace Models;
	use ActiveRecord;

class Amounts extends ActiveRecord\Model
{
	static $table_name = 'amounts';
	static $connection = CONNECTION;
	/*static $alias_attribute = array(
		'DirID' => 'dirid', 
		'ClassID'=>'classid',
		'GrID'=>'grid'
		);*/
}