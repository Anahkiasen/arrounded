<?php
namespace Arrounded\Traits;

use Validator;

/**
 * A self validating model
 */
trait SelfValidating
{
	/**
	 * Eventual errors gathered during validation
	 *
	 * @var MessageBag
	 */
	protected $errors;

	/**
	 * Whether the model should validate itself
	 *
	 * @var boolean
	 */
	protected $validating = true;

	/**
	 * Validates the model
	 *
	 * @param Validator $validation A validator instance to use
	 *
	 * @return boolean
	 */
	public function isValid($validation = null)
	{
		// If we already validated in and found errors, cancel
		if ($this->errors) {
			return false;
		}

		// If no rules, then valid by default
		if (empty(static::$rules) or !$this->validating) {
			return true;
		}

		// Validate the model
		if (!$validation) {
			$validation = Validator::make($this->attributes, $this->getRules());
		}

		// Store encountered errors
		$isValid = $validation->passes();
		if (!$isValid) {
			$this->errors = $validation->errors();
		}

		return $isValid;
	}

	/**
	 * Get the validation errors
	 *
	 * @return MessageBag
	 */
	public function getErrors()
	{
		return $this->errors;
	}

	/**
	 * Change the validating state
	 *
	 * @param boolean $validating
	 */
	public function setValidating($validating)
	{
		$this->validating = $validating;

		return $this;
	}

	/**
	 * Forces a model to save and bypass validation
	 *
	 * @return boolean
	 */
	public function forceSave()
	{
		$this->validating = false;
		$save = $this->save();
		$this->validating = true;

		return $save;
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the validation rules for a particular model
	 *
	 * @return array
	 */
	public function getRules()
	{
		$rules = static::$rules;
		foreach ($rules as $key => $rule) {
			preg_match_all('/\{([a-z_]+)\}/', $rule, $attributes);
			foreach ($attributes[1] as $attribute) {
				$rule = str_replace('{'.$attribute.'}', $this->$attribute, $rule);
			}

			$rules[$key] = $rule;
		}

		return $rules;
	}
}