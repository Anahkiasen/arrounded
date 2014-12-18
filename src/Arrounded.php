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

	/**
	 * @type string
	 */
	protected $modelsNamespace;

	/**
	 * A cache of found instances
	 *
	 * @type array
	 */
	protected $cached = [];

	//////////////////////////////////////////////////////////////////////
	///////////////////////// GETTERS AND SETTERS ////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * @param string $namespace
	 */
	public function setNamespace($namespace)
	{
		$this->namespace       = $namespace;
		$this->modelsNamespace = $namespace.'\Models';
	}

	/**
	 * @return string
	 */
	public function getNamespace()
	{
		return $this->namespace;
	}

	/**
	 * @return string
	 */
	public function getModelsNamespace()
	{
		return $this->modelsNamespace;
	}

	/**
	 * @param string $modelsNamespace
	 */
	public function setModelsNamespace($modelsNamespace)
	{
		$this->modelsNamespace = $modelsNamespace;
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
		// Check for cached instance
		$hash = $model.$type;
		if (array_key_exists($hash, $this->cached)) {
			return $this->cached[$hash];
		}

		// Look into possible namespaces
		$namespace = Str::plural($type);
		$service   = $this->getFirstExistingClass(array(
			sprintf('%s\%s\%s%s', $this->modelsNamespace, $namespace, $model, $type),
			sprintf('%s\%s\%s%s', $this->namespace, $namespace, $model, $type),
		));

		// Switch to default if not found
		if (!class_exists($service) && $defaults) {
			$service = $this->getFirstExistingClass($defaults);
		}

		// Cancel if the class doesn't exist
		$this->cached[$hash] = $service;
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
		$service = $this->getModelService($model, $type, $defaults);
		if (!$service) {
			return;
		}

		return $this->app->make($service);
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
		$default = sprintf('%s\%s', $this->modelsNamespace, $name);
		if (class_exists($default)) {
			return $default;
		}

		$repository = $this->getRepository($name);

		return $repository ? $repository->getModel() : null;
	}

	//////////////////////////////////////////////////////////////////////
	////////////////////////////// HELPERS ///////////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * Get the first existing class in an array
	 *
	 * @param string[] $classes
	 *
	 * @return string
	 */
	protected function getFirstExistingClass(array $classes)
	{
		$classes = (array) $classes;
		$classes = array_filter($classes, 'class_exists');

		return head($classes);
	}
}
