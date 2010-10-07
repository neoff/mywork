<?php
/**
 * модель БД регионы 
 * Более подробное описание или комментарии по поводу содержимого
 * файла (опционально)
 *
 * @package    model
 * @subpackage Regions
 * @since      07.10.2010 10:20:00
 * @author     enesterov
 * @category   model
 */

class Regions extends ActiveRecord\Model
{
	// explicit table name since our table is not "books"
	static $table_name = 'regions';

	// explicit pk since our pk is not "id"
	static $primary_key = 'id';

	// explicit connection name since we always want production with this model
	static $connection = CONNECTION;

	// explicit database name will generate sql like so => db.table_name
	static $db = 'test';
}