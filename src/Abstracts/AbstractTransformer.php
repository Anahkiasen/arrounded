<?php
namespace Arrounded\Abstracts;

use Arrounded\Collection;
use Arrounded\Services\Transformers\DefaultTransformer;
use Illuminate\Support\Str;
use League\Fractal\TransformerAbstract;

class AbstractTransformer extends TransformerAbstract
{
	//////////////////////////////////////////////////////////////////////
	////////////////////////// SMART TRANSFORMER /////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * Getter for availableIncludes
	 *
	 * @return array
	 */
	public function getAvailableIncludes()
	{
		if ($this->availableIncludes) {
			return $this->availableIncludes;
		}

		// Reflect on class
		$reflection = class_basename($this);
		$reflection = str_replace('Transformer', null, $reflection);
		$model      = app('arrounded')->qualifyModel($reflection);
		if (!class_exists($model)) {
			return $this->availableIncludes;
		}

		$relations = new $model;
		$relations = $relations->getAvailableRelations();

		return $this->availableIncludes = $relations;
	}

	/**
	 * Generate include methods
	 *
	 * @param string $name
	 * @param array  $arguments
	 *
	 * @return \League\Fractal\Resource\Collection|\League\Fractal\Resource\Item
	 */
	public function __call($name, $arguments)
	{
		if (Str::startsWith($name, 'include')) {
			return $this->includeRelation($name, $arguments[0]);
		}
	}

	/**
	 * Include any relation from the model
	 *
	 * @param string        $name
	 * @param AbstractModel $item
	 *
	 * @return \League\Fractal\Resource\Collection|\League\Fractal\Resource\Item
	 */
	protected function includeRelation($name, AbstractModel &$item)
	{
		$relation = str_replace('include', null, $name);
		$relation = lcfirst($relation);

		// If the item is a collection, eager load all related
		if ($item instanceof Collection && method_exists($item, $relation)) {
			$item->load($relation);
		}

		// Load item
		if ($related = $item->$relation) {
			if ($related instanceof Collection) {
				$transformer = $related->first() ? $related->first()->getTransformer() : new DefaultTransformer();

				return $this->collection($related, $transformer);
			} else {
				return $this->item($related, $related->getTransformer());
			}
		}
	}

	//////////////////////////////////////////////////////////////////////
	////////////////////////////// DEFAULTS //////////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * Transform a model for the API
	 *
	 * @param AbstractModel $model
	 * @param Callable      $callback
	 *
	 * @return array
	 */
	public function transformWithDefaults(AbstractModel $model, callable $callback = null)
	{
		$attributes = $callback ? $callback($model) : $model->toArray();

		return array_merge(array(
			'id' => (int) $model->id,
		), $attributes, array(
			'created_at' => (string) $model->created_at->toDateTimeString(),
		));
	}
}
