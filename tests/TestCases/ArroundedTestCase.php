<?php
namespace Arrounded\TestCases;

use Arrounded\Facades\Arrounded;
use Arrounded\Traits\UsesContainer;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Mockery;
use PHPUnit_Framework_TestCase;

abstract class ArroundedTestCase extends PHPUnit_Framework_TestCase
{
    use UsesContainer;

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Setup the tests.
     */
    public function setUp()
    {
        $this->app = new Container();

        $this->app->singleton('files', Filesystem::class);

        $this->app->singleton('Arrounded\Arrounded', 'Arrounded\Arrounded');
        $this->app->alias('Arrounded\Arrounded', 'arrounded');

        $this->app->instance(Container::class, $this->app);

        Arrounded::setFacadeApplication($this->app);
    }

    /**
     * Remove existing mocks.
     */
    public function tearDown()
    {
        Mockery::close();
    }
}
