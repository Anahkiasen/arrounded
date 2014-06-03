<?php
namespace Arrounded\Abstracts;

use Arrounded\Validation\ValidationException;
use Illuminate\Validation\Factory as Validator;

/**
 * A class representation of a form
 */
abstract class AbstractForm
{
	/**
	 * The validator factory
	 *
	 * @var Validator
	 */
	protected $validator;

	/**
	 * The validation rules
	 *
	 * @var array
	 */
	protected $rules = [];

	/**
	 * Build a new form
	 *
	 * @param Validator $validator
	 * @param Request   $request
	 */
	public function __construct(Validator $validator)
	{
		$this->validator = $validator;
	}

	/**
	 * Validate an array of attributes
	 *
	 * @param array $attributes
	 *
	 * @return void
	 */
	public function validate(array $attributes = array())
	{
		// Get attributes and create Validator
		$validation = $this->validator->make($attributes, $this->rules);

		if ($validation->fails()) {
			throw new ValidationException('Validation failed', $validation->getMessageBag());
		}
	}

	/**
	 * Get the rules in use
	 *
	 * @return array
	 */
	public function getRules()
	{
		return $this->rules;
	}
}