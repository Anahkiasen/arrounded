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
	 *
	 * @return void
	 */
	protected function setJsonAttribute($attribute, $value)
	{
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
		$attribute = json_decode($attribute, true);

		return array_merge($defaults, $attribute);
	}
}
