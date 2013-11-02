<?php
namespace Arrounded\Testing;

use Illuminate\Foundation\Testing\TestCase as IlluminateTestCase;
use Mockery;
use Redirect;

class TestCase extends IlluminateTestCase
{
	/**
	 * Remove mocked instances on close
	 *
	 * @return void
	 */
	public function tearDown()
	{
		// Remove mocked instances
		Mockery::close();

		// Close connection
		unset($this->app['db']);
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Spoof the Redirect::back method
	 *
	 * @param  string $endpoint
	 *
	 * @return void
	 */
	protected function spoofRedirectBack($endpoint = '/')
	{
		$redirect = Redirect::to($endpoint);
		Redirect::shouldReceive('back')->andReturn($redirect);
	}

	/**
	 * Mock a class and inject it into the container
	 *
	 * @param  string  $class
	 * @param  Closure $expectations
	 *
	 * @return void
	 */
	protected function mock($class, $expectations)
	{
		$mock = Mockery::mock($class);
		$mock = $expectations($mock)->mock();

		$this->app->instance($class, $mock);
	}
}