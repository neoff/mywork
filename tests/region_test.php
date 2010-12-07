<?php
/**
 * unit test Regions
 *
 * @package    tests
 * @subpackage Regions
 * @since      07.10.2010 10:20:00
 * @author     enesterov
 */

	require_once 'PHPUnit/Framework/TestCase.php';
	require_once 'SnakeCase_PHPUnit_Framework_TestCase.php';
	require_once dirname(__FILE__) . '/../lib/ActiveRecord.php';
	

	$GLOBALS['slow_tests'] = false;
	
	
	ActiveRecord\Config::initialize(function($cfg)
	{
		$cfg->set_model_directory(realpath(dirname(__FILE__) . '/../models'));
		$cfg->set_default_connection('test');
	}
	// create some people
	/*$region = new Regions(array('name' => 'Москва', 'mvideo_region_id' => 1));
	$region->save();
	
	// compact way to create and save a model
	$tito = Regions::create(array('name' => 'Ростов-на-Дону', 'mvideo_region_id' => 0));
	
	print_r(Regions::first());*/