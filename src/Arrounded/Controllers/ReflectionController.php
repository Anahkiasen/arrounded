<?php
namespace Arrounded\Controllers;

use ReflectionClass;
use Illuminate\Support\Str;

/**
 * A class to compute additional informations around a Controller
 */
class ReflectionController extends ReflectionClass
{
	////////////////////////////////////////////////////////////////////
	///////////////////////////////// META /////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the model related to the controller
	 *
	 * @return string
	 */
	public function model()
	{
		$model = $this->getShortName();
		$model = str_replace('Controller', null, $model);
		$model = Str::singular($model);

		return $model;
	}

	/**
	 * Get the resource associated with the controller
	 *
	 * @return string
	 */
	public function resource()
	{
		$resource = str_replace('Controller', null, $this->getName());
		$resource = str_replace('\\', '.', $resource);

		return strtolower($resource);
	}

	////////////////////////////////////////////////////////////////////
	////////////////////////////// INSTANCES ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get an instance of the model
	 *
	 * @return Model
	 */
	public function newModel()
	{
		$model = $this->model();

		return new $model;
	}
}