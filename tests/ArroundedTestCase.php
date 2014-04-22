<?php
namespace Arrounded;

use Illuminate\Container\Container;
use Mockery;
use PHPUnit_Framework_TestCase;

abstract class ArroundedTestCase extends PHPUnit_Framework_TestCase
{
	/**
	 * The tests container
	 *
	 * @var Container
	 */
	protected $app;

	/**
	 * Remove existing mocks
	 *
	 * @return void
	 */
	public function tearDown()
	{
		Mockery::close();
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// MOCKERY ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Mock a Cache facade
	 *
	 * @return Mockery
	 */
	protected function mockCache()
	{
		if (class_exists('Cache')) {
			return;
		}

		$cache = Mockery::mock('alias:Cache');
		$cache->shouldReceive('rememberForever')->andReturnUsing(function ($name, $closure) {
			return $closure();
		});

		return $cache;
	}
}
