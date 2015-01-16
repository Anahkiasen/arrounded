<?php
namespace Arrounded\TestCases;

use Illuminate\Container\Container;
use Mockery;
use PHPUnit_Framework_TestCase;

abstract class ArroundedTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * The tests container
     *
     * @type Container
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
}
