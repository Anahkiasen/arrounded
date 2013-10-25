<?php
use Arrounded\Traits\SelfValidating;

class SelfValidatingTest extends ArroundedTests
{
	public function testCanCheckValidModel()
	{
		$validator = Mockery::mock('Validator');
		$validator->shouldReceive('passes')->once()->andReturn(true);

		$validModel = new DummyValidatingModel(array(
			'name' => 'foobar',
		));
		$this->assertTrue($validModel->isValid($validator));
	}

	public function testCanCheckInvalidModel()
	{
		$errors = array('name' => 'The name is required');

		$validator = Mockery::mock('Validator');
		$validator->shouldReceive('passes')->once()->andReturn(false);
		$validator->shouldReceive('errors')->once()->andReturn($errors);

		$invalidModel = new DummyValidatingModel(array(
			'name' => '',
		));

		$this->assertFalse($invalidModel->isValid($validator));
		$this->assertEquals($errors, $invalidModel->getErrors());
	}

	public function testCanDisableValidation()
	{
		$validator = Mockery::mock('Validator');
		$validator->shouldReceive('passes')->never();

		$invalidModel = new DummyValidatingModel(array(
			'name' => '',
		));

		$this->assertTrue($invalidModel->setValidating(false)->isValid($validator));
	}

	public function testDoesntValidateModelsWithNoRules()
	{
		$validator = Mockery::mock('Validator');
		$validator->shouldReceive('passes')->never();

		$invalidModel = new DummyValidatingNoRulesModel(array(
			'name' => '',
		));

		$this->assertTrue($invalidModel->isValid($validator));
	}
}

// Dummies
//////////////////////////////////////////////////////////////////////

class DummyValidatingModel extends Illuminate\Database\Eloquent\Model
{
	use SelfValidating;

	public static $rules = array(
		'name' => 'required',
	);

	protected $guarded = array();
}

class DummyValidatingNoRulesModel extends Illuminate\Database\Eloquent\Model
{
	use SelfValidating;

	protected $guarded = array();
}