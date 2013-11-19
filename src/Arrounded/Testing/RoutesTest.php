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
	 * A list of URLs that redirect back
	 *
	 * @var array
	 */
	protected $redirectBack = array();

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

		// Spoof redirect back
		$redirectsBack = $this->redirectsBack($route);
		if ($redirectsBack) {
			$this->spoofRedirectBack();
		}

		// Call route and assert correct status
		$this->call('GET', $route);
		if ($redirectsBack) {
			$this->assertRedirectedTo('/');
		} else {
			$this->assertResponseOk();
		}
	}

	/**
	 * Checks if an URL redirects back
	 *
	 * @param string $route
	 *
	 * @return boolean
	 */
	protected function redirectsBack($route)
	{
		$route   = str_replace($this->app['url']->to('/').'/', null, $route);
		$pattern = implode('$|^', $this->redirectBack);
		$pattern = '#(^' .$pattern. '$)#';

		return preg_match($pattern, $route);
	}
}
