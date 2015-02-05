<?php
namespace Arrounded;

use Arrounded\TestCases\ArroundedTestCase;
use Mockery;
use Mockery\Mock;

class ArroundedTest extends ArroundedTestCase
{
	public function testCanQualifyClass()
	{
		$this->assertEquals('Foobar', $this->arrounded->qualifyClass('Foobar'));
		$this->assertEquals('Composers\Foobar', $this->arrounded->qualifyClass('Foobar', 'Composers'));

		$this->arrounded->setRootNamespace('Arrounded');
		$this->assertEquals('Arrounded\Foobar', $this->arrounded->qualifyClass('Foobar'));
		$this->assertEquals('Arrounded\Composers\Foobar', $this->arrounded->qualifyClass('Foobar', 'Composers'));

		$this->arrounded->setNamespaces(['Composers' => 'Http']);
		$this->assertEquals('Arrounded\Foobar', $this->arrounded->qualifyClass('Foobar'));
		$this->assertEquals('Arrounded\Http\Composers\Foobar', $this->arrounded->qualifyClass('Foobar', 'Composers'));

		$this->arrounded->setNamespaces(['Composers' => null]);
		$this->assertEquals('Arrounded\Composers\Foobar', $this->arrounded->qualifyClass('Foobar', 'Composers'));
	}

	public function testCanGetFirstExistingClass()
	{
		$this->assertEquals('Arrounded\Arrounded', $this->arrounded->getFirstExistingClass('Arrounded\Arrounded'));
		$this->assertEquals('Arrounded\Arrounded', $this->arrounded->getFirstExistingClass(['Foo\Bar', 'Arrounded\Arrounded']));
	}

	public function testCanGetModelService()
	{
		$this->arrounded->setNamespace('Arrounded');
		$this->arrounded->setNamespaces(['Repositories' => null]);

		$repository = $this->arrounded->getModelService('Upload', 'Repositories');
		$this->assertEquals('Arrounded\Repositories\UploadsRepository', $repository);
	}

	public function testCanGetFolder()
	{
		$this->app['files'] = Mockery::mock('Illuminate\Filesystem\Filesystem', ['isDirectory' => true])->makePartial();

		$this->arrounded->setNamespace('Arrounded');
		$this->app['path'] = __DIR__.'/../src';

		$folder = $this->arrounded->getFolder('Foobar');
		$this->assertEquals(__DIR__.'/../src/Arrounded/Foobar', $folder);

		$folder = $this->arrounded->getFolder('Foobar\Baz');
		$this->assertEquals(__DIR__.'/../src/Arrounded/Foobar/Baz', $folder);

		$this->arrounded->setNamespaces(['Composers' => 'Http']);
		$folder = $this->arrounded->getFolder('Composers');
		$this->assertEquals(__DIR__.'/../src/Arrounded/Http/Composers', $folder);
	}
}
