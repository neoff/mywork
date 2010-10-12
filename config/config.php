<?php
/**
 * Файл конфигурации fast_cgi 
 * необходимо добавить коонекты к другим БД
 * (опционально)
 *
 * @since      07.10.2010 10:12:00
 * @author     enesterov
 * @category   none
 */

	ActiveRecord\Config::initialize(function($cfg)
	{
		$cfg->set_model_directory('.');
		$cfg->set_connections(array(
			'test' => 'mysql://root:123456@localhost/test',
			'develop' => 'mysql://baseadmin:C0ffeAmerikan0@192.168.1.226:33306/mvideo',
			'develop_test' => 'mysql://baseadmin:C0ffeAmerikan0@localhost:33306/mvideo',
		));
	});