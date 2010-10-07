<?php
/**
 * Файл конфигурации fast_cgi 
 * необходимо добавить коонекты к другим БД
 * (опционально)
 *
 * @package    config
 * @subpackage config
 * @since      07.10.2010 10:12:00
 * @author     enesterov
 * @category   none
 */


	require_once ROOT_PATH . '/lib/ActiveRecord.php';

	ActiveRecord\Config::initialize(function($cfg)
	{
		$cfg->set_model_directory('.');
		$cfg->set_connections(array(
			'test' => 'mysql://root:123456@localhost/test',
			'develop' => 'mysql://root:123456@localhost/test',
			'deploy' => 'none'
		));
	});