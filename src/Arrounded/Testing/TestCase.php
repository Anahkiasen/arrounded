<?php
namespace Arrounded\Testing;

use Auth;
use Illuminate\Foundation\Testing\TestCase as IlluminateTestCase;
use Mockery;
use Redirect;
use User;

class TestCase extends IlluminateTestCase
{
	/**
	 * Recreate the database
	 *
	 * @return void
	 */
	protected function recreateDatabase()
	{
		if (!Schema::hasTable('migrations')) {
			Artisan::call('migrate:install');
			Artisan::call('migrate');
		}

		$this->seedDatabase();
		Eloquent::reguard();
	}

	/**
	 * Seed the database with dummy data
	 *
	 * @return void
	 */
	protected function seedDatabase()
	{
		// ...
	}

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
	/////////////////////////// AUTHENTIFICATION ///////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Authentify as an User
	 *
	 * @return User
	 */
	public function authentify($user = null)
	{
		$user = $user ?: User::first();
		if (!$user) {
			return;
		}

		// Log in
		$this->be($user);
		Auth::setUser($user);

		return $user;
	}

	/**
	 * Logout the user
	 *
	 * @return void
	 */
	public function logout()
	{
		$this->app['auth']->logout();
		Auth::logout();
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