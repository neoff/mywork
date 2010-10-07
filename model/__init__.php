<?php

require_once dirname(__FILE__) . '/../lib/php-activerecord/ActiveRecord.php'


ActiveRecord\Config::initialize(function($cfg)
{
	$cfg->set_model_directory(dirname(__FILE__) . '/');
	$cfg->set_connections(array(
		'development' => 'sqlite://'. ROOTPATH .'/tests/database_name.db'));
});