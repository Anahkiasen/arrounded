<?php
namespace Arrounded\Testing;

use Artisan;
use Auth;
use Closure;
use DB;
use Eloquent;
use Illuminate\Auth\UserInterface;
use Illuminate\Foundation\Testing\TestCase as IlluminateTestCase;
use Mockery;
use Redirect;
use Schema;
use User;

class TestCase extends IlluminateTestCase
{
    /**
     * Some aliases for mocks
     *
     * @type array
     */
    protected $namespaces = array(
        'app'    => '',
        'models' => '',
    );

    /**
     * Creates the application.
     * Needs to be implemented by subclasses.
     *
     * @return Symfony\Component\HttpKernel\HttpKernelInterface
     */
    public function createApplication()
    {
        // Create application
        $unitTesting     = true;
        $testEnvironment = 'testing';

        return require __DIR__.'/../../../../../bootstrap/start.php';
    }

    ////////////////////////////////////////////////////////////////////
    ///////////////////////////// ASSERTIONS ///////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Assert a variable in the view is an instance of something
     *
     * @param string $variables
     *
     * @return mixed
     */
    public function getFromView($variables)
    {
        $variables = (array) $variables;
        $response  = $this->client->getResponse()->original;

        $data = array();
        foreach ($variables as $variable) {
            $this->assertViewHas($variable);
            $data[$variable] = $response->$variable;
        }

        if (count($data) == 1) {
            return reset($data);
        }

        return $data;
    }

    /**
     * Assert a variable in the view is not empty
     *
     * @param string $data
     *
     * @return Assertion
     */
    public function assertViewDataNotEmpty($data)
    {
        return $this->assertNotEmpty($this->getFromView($data));
    }

    ////////////////////////////////////////////////////////////////////
    //////////////////////////// TESTS LIFETIME ////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Recreate the database
     *
     * @return void
     */
    protected function recreateDatabase()
    {
        // Migrate and seed
        if (!Schema::hasTable('migrations')) {
            Artisan::call('migrate:install');
            Artisan::call('migrate');
            $this->seedDatabase();
        }

        // Start a transaction
        DB::beginTransaction();

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

        // Rollback changes to the database
        DB::rollback();
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////// AUTHENTIFICATION ///////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Authentify as an User
     *
     * @param UserInterface $user
     *
     * @return UserInterface
     */
    public function authentify(UserInterface $user = null)
    {
        $model = $this->namespaces['models'].'User';
        if (!class_exists($model)) {
            return;
        }

        // Find first User
        $user = $user ?: $model::first();
        if (!$user) {
            return;
        }

        // Log in
        $this->be($user);
        Auth::setUser($user);

        return $user;
    }

    /**
     * Get the test user
     *
     * @return UserInterface
     */
    public function testUser()
    {
        return $this->app['auth']->user();
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
    /////////////////////////////// REQUESTS ///////////////////////////
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

    ////////////////////////////////////////////////////////////////////
    /////////////////////////////// MOCKING ////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Mock a repository
     *
     * @param sring   $repository
     * @param Closure $expectations
     */
    protected function mockRepository($repository, Closure $expectations)
    {
        $mocked = $this->getMockedClass('Repositories\\'.$repository.'Repository');

        return $this->mock($mocked, $expectations);
    }

    /**
     * Mock a class and inject it into the container
     *
     * @param  string  $class
     * @param  Closure $expectations
     */
    protected function mock($class, $expectations)
    {
        $mock = Mockery::mock($class);
        $mock = $expectations($mock)->mock();

        $this->app->instance($class, $mock);
    }

    /**
     * Get the full path to a mocked class
     *
     * @param string $class
     *
     * @return string
     */
    protected function getMockedClass($class)
    {
        return $this->namespaces['app'].'\\'.$class;
    }
}
