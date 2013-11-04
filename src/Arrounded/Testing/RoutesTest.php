<?php
namespace Arrounded\Testing;

use Arrounded\Testing\Crawler;
use Artisan;

class RoutesTest extends \TestCase
{
	/**
	 * The routes to ignore
	 *
	 * @var array
	 */
	protected $ignored = array(
		'_profiler',
		'logout',
	);

	/**
	 * The additional routes
	 *
	 * @var array
	 */
	protected $additional = array();

	/**
	 * Recreate the database before each test
	 *
	 * @return void
	 */
	public function setUp()
	{
		$this->refreshApplication();
		$this->recreateDatabase();
	}

	/**
	 * Seed the current database
	 *
	 * @return void
	 */
	protected function seedDatabase()
	{
		Artisan::call('db:seed');
	}

	/**
	 * Provide the routes to test
	 *
	 * @return array
	 */
	public function provideRoutes()
	{
		// Set up database
		$this->setUp();

		// Get the routes to call
		$crawler = new Crawler($this->app);
		$crawler->setIgnored($this->ignored);

		return $crawler->provideRoutes($this->additional);
	}

	/**
	 * @dataProvider provideRoutes
	 */
	public function testCanAccessRoutes($route)
	{
		// Authentify user
		$this->authentify();

		$this->call('GET', $route);
		$this->assertResponseOk();
	}
}
