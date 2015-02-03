<?php
namespace Arrounded;

use Arrounded\TestCases\ArroundedTestCase;

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
}
