<?php
namespace Arrounded\Traits;

use Illuminate\Support\Contracts\ArrayableInterface;

/**
 * A serializable element
 */
trait Serializable
{
	/**
	 * Serialize an entity and cast some attributes
	 *
	 * @param ArrayableInterface|array $entity
	 *
	 * @return array
	 */
	public function serializeEntity($entity)
	{
		// Cast the entity itself
		if ($entity instanceof ArrayableInterface) {
			$entity = $entity->toArray();
		}

		// Cast attributes
		if ($this->casts) {
			foreach ($this->casts as $type => $attributes) {
				$entity = $this->castAttributes($entity, $attributes, $type);
			}
		}

		return $entity;
	}

	/**
	 * Convert an array of properties to other types
	 *
	 * @param array  $attributes
	 * @param array  $toConvert
	 * @param string $type
	 *
	 * @return array
	 */
	public function castAttributes(array $attributes, $toConvert, $type = 'boolean')
	{
		$toConvert = (array) $toConvert;
		foreach ($toConvert as $converted) {
			if (isset($attributes[$converted])) {
				settype($attributes[$converted], $type);
			}
		}

		return $attributes;
	}
}
