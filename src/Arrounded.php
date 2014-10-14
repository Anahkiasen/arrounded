<?php
namespace Arrounded;

use Arrounded\Abstracts\AbstractRepository;
use Arrounded\Traits\UsesContainer;
use Illuminate\Support\Str;

class Arrounded
{
	use UsesContainer;

	/**
	 * The application's namespace
	 *
	 * @type string
	 */
	protected $namespace;

	//////////////////////////////////////////////////////////////////////
	///////////////////////// GETTERS AND SETTERS ////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * @param string $namespace
	 */
	public function setNamespace($namespace)
	{
		$this->namespace = $namespace;
	}

	/**
	 * @return string
	 */
	public function getNamespace()
	{
		return $this->namespace;
	}

	//////////////////////////////////////////////////////////////////////
	///////////////////////////// REFLECTION /////////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * Get a model service
	 *
	 * @param string            $model
	 * @param string            $type
	 * @param string|array|null $defaults
	 *
	 * @return string
	 */
	public function getModelService($model, $type, $defaults = null)
	{
		$service = sprintf('%s\%s\%s%s', $this->namespace, Str::plural($type), $model, $type);

		$defaults = (array) $defaults;
		$defaults = array_filter($defaults, 'class_exists');
		if (!class_exists($service) && $defaults) {
			$service = head($defaults);
		}

		// Cancel if the class doesn't exist
		if (!class_exists($service)) {
			return;
		}

		return $service;
	}

	/**
	 * Build a model service
	 *
	 * @param string            $model
	 * @param string            $type
	 * @param string|array|null $defaults
	 *
	 * @return object
	 */
	public function buildModelService($model, $type, $defaults = null)
	{
		return $this->app->make($this->getModelService($model, $type, $defaults));
	}

	/**
	 * @param string $model
	 *
	 * @return AbstractRepository
	 */
	public function getRepository($model)
	{
		$model = str_replace('Repository', null, $model);
		$model = Str::plural($model);

		return $this->buildModelService($model, 'Repository');
	}

	/**
	 * Find the fully qualified name of a model by its short name
	 *
	 * @param string $name
	 *
	 * @return string|null
	 */
	public function qualifyModel($name)
	{
		$name = trim($name, '\\');
		$name = ucfirst($name);

		// Look into default path
		$default = sprintf('%s\Models\%s', $this->namespace, $name);
		if (class_exists($default)) {
			return $default;
		}

		$repository = $this->getRepository($name);

		return $repository ? $repository->getModel() : null;
	}
}
