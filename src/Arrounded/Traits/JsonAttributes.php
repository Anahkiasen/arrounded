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
		$defaults = $defaults ?: $this->getAttributeDefault($attribute);
		$value    = array_replace_recursive((array) $defaults, $value);
		$value    = json_encode($value);

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
		$value = array_get($this->attributes, $attribute, '[]');

		// Decode and merge with defaults
		$defaults = !empty($defaults) ? $defaults : $this->getAttributeDefault($attribute);
		$value    = json_decode($value, true);
		$value    = array_replace_recursive($defaults, $value);

		return $value;
	}

	/**
	 * Get the default value of an attribute
	 *
	 * @param  string $attribute
	 *
	 * @return mixed
	 */
	protected function getAttributeDefault($attribute)
	{
		if (!$this->defaults) {
			return;
		}

		return (array) array_get($this->defaults, $attribute);
	}
}
