<?php
namespace Arrounded\Macros;

use Former;
use Arrounded\Traits\UsesContainer;
use Illuminate\Support\Str;

/**
 * Injects macros into the current Former instance
 */
class FormerBuilder
{
	use UsesContainer;

	/**
	 * Register multiple macros at once
	 *
	 * @param array $macros
	 *
	 * @return void
	 */
	public function registerMacros(array $macros = array())
	{
		// Merge default macros
		$class  = get_class($this);
		$macros = array_merge(['gender', 'boolean', 'belongsTo', 'manyToMany'], $macros);

		// Register macros
		foreach ($macros as $name) {
			$this->app['former']->macro($name, $class.'@'.$name);
		}
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// MACROS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Generates a gender picker
	 *
	 * @return Select
	 */
	public function gender()
	{
		return $this->former->select('gender')->options(['Male', 'Female'])->placeholder('Gender');
	}

	/**
	 * Generates a boolean-type select
	 *
	 * @param string $name
	 * @param string $label
	 *
	 * @return Select
	 */
	public function boolean($name, $label = null)
	{
		return $this->former->select($name, $label)->options(['No', 'Yes']);
	}

	/**
	 * A select for a model belonging to another
	 *
	 * @param string $model
	 *
	 * @return Select
	 */
	public function belongsTo($model, $foreign = null)
	{
		$users   = $this->getEntries($model);
		$foreign = $foreign ?: strtolower($model).'_id';

		return $this->former->select($foreign, $model)->options($users);
	}

	/**
	 * Generates a field group to pick one or more related models
	 *
	 * @param string $name
	 *
	 * @return Group
	 */
	public function manyToMany($name)
	{
		$options = $this->getEntries($name, 'id');

		// Format entries
		foreach ($options as $key => $value) {
			$options[$key] = sprintf('[%d] %s', $key, $value);
		}

		// Fetch entries from model
		$entries = array();
		if ($relation = $this->former->getValue($name)) {
			$entries = $relation->lists('id');
		}

		return $this->former->multiselect($name)->options($options, $entries);
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get all models as a dictionary, alphabetically ordered
	 *
	 * @param string $model
	 *
	 * @return array
	 */
	protected function getEntries($model, $orderBy = 'id')
	{
		$model = Str::singular($model);

		return $model::orderBy($orderBy, 'ASC')->get()->lists('name', 'id');
	}
}
