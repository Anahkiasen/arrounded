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

	//////////////////////////////////////////////////////////////////////
	///////////////////////////// REFLECTION /////////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * Get a model service
	 *
	 * @param string      $model
	 * @param string      $type
	 * @param string|null $default
	 *
	 * @return object
	 */
	public function getModelService($model, $type, $default = null)
	{
		$service = sprintf('%s\%s\%s%s', $this->namespace, Str::plural($type), $model, $type);
		if (!class_exists($service) && $default) {
			$service = $default;
		}

		return $this->app->make($service);
	}

	/**
	 * @param $model
	 * @param $type
	 *
	 * @return mixed
	 */
	public function getModelServiceInstance($model, $type)
	{
		return $this->app->make($this->getModelService($model, $type));
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

		return $this->getModelService($model, 'Repository');
	}

	/**
	 * Find the fully qualified name of a model by its short name
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public function qualifyModelByName($name)
	{
		$name = trim($name, '\\');
		$name = ucfirst($name);

		// Look into default path
		$default = sprintf('%s\Models\%s', $this->namespace, $name);
		if (class_exists($default)) {
			return $default;
		}

		return $this->getRepositoryFromModel($name)->getModel();
	}
}
