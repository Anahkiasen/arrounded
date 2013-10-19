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
	 * @return boolean
	 */
	public function isValid()
	{
		// If we already validated in and found errors, cancel
		if ($this->errors) {
			return false;
		}

		// If no rules, then valid by default
		if (!static::$rules or $this->validates) {
			return true;
		}

		// Validate the model
		$validation = Validator::make($this->attributes, static::$rules);
		$isValid    = $validation->passes();

		// Store encountered errors
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
	 * Forces a model to save and bypass validation
	 *
	 * @return boolean
	 */
	public function forceSave()
	{
		$this->validates = false;
		$save = $this->save();
		$this->validates = true;

		return $save;
	}
}