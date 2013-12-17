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
	 *
	 * @return array
	 */
	protected function getJsonAttribute($attribute)
	{
		$attribute = array_get($this->attributes, $attribute, '[]');

		return json_decode($attribute, true);
	}
}
