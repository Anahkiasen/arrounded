<?php
use Illuminate\Container\Container;

abstract class ArroundedTests extends PHPUnit_Framework_TestCase
{
	protected $app;

	public static function setUpBeforeClass()
	{
		static::mockCache();
	}

	public static function tearDownAfterClass()
	{
		Mockery::close();
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// MOCKERY ////////////////////////////
	////////////////////////////////////////////////////////////////////

	public static function mockCache()
	{
		if (class_exists('Cache')) {
			return;
		}

		$cache = Mockery::mock('alias:Cache');
		$cache->shouldReceive('rememberForever')->andReturnUsing(function($name, $closure) {
			return $closure();
		});

		return $cache;
	}
}