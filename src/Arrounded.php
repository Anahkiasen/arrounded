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
	 * @param string $model
	 * @param string $type
	 *
	 * @return object
	 */
	public function getModelService($model, $type)
	{
		$instance = sprintf('%s\%s\%s%s', $this->namespace, Str::plural($type), $model, $type);
		$instance = $this->app->make($instance);

		return $instance;
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
		$repository = $this->getRepositoryFromModel($name);

		return $repository->getModel();
	}
}
