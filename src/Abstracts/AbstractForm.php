<?php
namespace Arrounded\Abstracts;

use Arrounded\Validation\ValidationException;
use Illuminate\Validation\Factory;
use Illuminate\Validation\Validator;

/**
 * A class representation of a form
 */
abstract class AbstractForm
{
	/**
	 * The validator factory
	 *
	 * @var Factory
	 */
	protected $validator;

	/**
	 * The validation rules
	 *
	 * @var array
	 */
	protected $rules = [];

	/**
	 * A model to fine-tune rules to
	 *
	 * @var AbstractModel
	 */
	protected $model;

	/**
	 * Build a new form
	 *
	 * @param Factory $validator
	 * @param Request $request
	 */
	public function __construct(Factory $validator)
	{
		$this->validator = $validator;
	}

	/**
	 * Validate an array of attributes
	 *
	 * @param array    $attributes
	 * @param Callable $callback
	 *
	 * @return mixed
	 */
	public function validate(array $attributes = array(), Callable $callback = null)
	{
		// Get attributes and create Validator
		$validation = $this->validator->make($attributes, $this->getRules());

		// Alter rules and stuff
		$validation = $this->alterValidation($validation);

		if ($validation->fails()) {
			throw new ValidationException('Validation failed', $validation->getMessageBag());
		} elseif ($callback) {
			return $callback($attributes, $this->model);
		}

		return true;
	}

	/**
	 * Validate an array of attributes for a particular model
	 *
	 * @param AbstractModel $model
	 * @param array         $attributes
	 * @param Callabke      $callback
	 *
	 * @return void
	 */
	public function validateFor(AbstractModel $model, array $attributes = array(), Callable $callback = null)
	{
		$this->model = $model;

		return $this->validate($attributes, $callback);
	}

	//////////////////////////////////////////////////////////////////////
	//////////////////////////////// RULES ///////////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * Alter the rules on the Validator, etc
	 *
	 * @param Validator $validation
	 *
	 * @return Validator
	 */
	protected function alterValidation(Validator $validation)
	{
		return $validation;
	}

	/**
	 * Get the rules in use
	 *
	 * @return array
	 */
	public function getRules()
	{
		$rules = $this->rules;
		if (!$this->model) {
			return $rules;
		}

		// Replace placeholders in rules
		foreach ($rules as $key => $rule) {
			preg_match_all('/\{([a-z_]+)\}/', $rule, $attributes);
			foreach ($attributes[1] as $attribute) {
				$rule = str_replace('{'.$attribute.'}', $this->model->$attribute, $rule);
			}

			$rules[$key] = $rule;
		}

		return $rules;
	}
}
