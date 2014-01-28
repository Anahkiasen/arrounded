<?php
namespace Arrounded\Traits;

/**
 * A model with JSON attributes
 */
trait JsonAttributes
{
	/**
	 * Encode an attribute before saving it
	 *
	 * @param  string $attribute
	 * @param  mixed  $value
	 * @param  array  $defaults
	 *
	 * @return void
	 */
	protected function setJsonAttribute($attribute, $value, array $defaults = array())
	{
		// Merge with defaults and encode
		$value = array_replace_recursive($defaults, $value);
		$value = json_encode($value);

		$this->attributes[$attribute] = json_encode($value);
	}

	/**
	 * Get a JSON attribute
	 *
	 * @param  string $attribute
	 * @param  array  $defaults
	 *
	 * @return array
	 */
	protected function getJsonAttribute($attribute, $defaults = array())
	{
		$attribute = array_get($this->attributes, $attribute, '[]');

		// Decode and merge with defaults
		$attribute = json_decode($attribute, true);
		$attribute = array_replace_recursive($defaults, $attribute);

		return $attribute;
	}
}
