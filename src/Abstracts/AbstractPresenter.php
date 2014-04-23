<?php
namespace Arrounded\Abstracts;

use HTML;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Robbo\Presenter\Presenter;
use Route;

/**
 * A base class for presenters with common attributes
 */
class AbstractPresenter extends Presenter
{
	/**
	 * Display a website as a link
	 *
	 * @return string
	 */
	public function presentWebsite()
	{
		return HTML::link($this->object->website);
	}

	/**
	 * Displays an email as a mailto link
	 *
	 * @return string
	 */
	public function presentEmail()
	{
		return HTML::mailto($this->object->email);
	}

	/**
	 * Present a gender attribute
	 *
	 * @return string
	 */
	public function presentGender()
	{
		return (int) $this->object->gender == 0 ? 'Male' : 'Female';
	}

	/**
	 * Present an object
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->model($this);
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// BOOLEANS ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Display the private status
	 *
	 * @return string
	 */
	public function presentPrivate()
	{
		return $this->boolean($this->object->private);
	}

	/**
	 * Display the featured status
	 *
	 * @return string
	 */
	public function presentFeatured()
	{
		return $this->boolean($this->object->featured);
	}

	/**
	 * Display the public status
	 *
	 * @return string
	 */
	public function presentPublic()
	{
		return $this->boolean($this->object->public);
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Format an attribute
	 *
	 * @param string|null $attribute
	 *
	 * @return mixed
	 */
	protected function getFromModel($attribute)
	{
		if (is_string($attribute)) {
			return $this->object->$attribute;
		}

		return $attribute;
	}

	/**
	 * Check if an action exists and has a route bound to it
	 *
	 * @param string $action
	 *
	 * @return boolean
	 */
	protected function actionExists($action)
	{
		return (bool) Route::getRoutes()->getByAction($action);
	}

	/**
	 * Show a model's main identifier
	 *
	 * @param string|Model|null $model
	 *
	 * @return string
	 */
	protected function model($model = null)
	{
		$model = $this->getFromModel($model);

		if (!$model) {
			return;
		}

		$name = $model->name;
		$name = $name ?: $model->getTable(). ' ' .$model->id;

		// Return straigt name if no route
		$route = 'admin.'.$model->getTable().'.edit';
		if (!$model->id or !Route::getRoutes()->hasNamedRoute($route)) {
			return $name;
		}

		return HTML::linkRoute($route, $name, $model->id);
	}

	/**
	 * Present a collection
	 *
	 * @param Collection $collection
	 *
	 * @return string
	 */
	protected function collection($collection)
	{
		return $this->getFromModel($collection)->implode('name', ', ');
	}

	/**
	 * Show the count of a collection and a page to see entries
	 *
	 * @param string $relation
	 *
	 * @return string
	 */
	protected function collectionCount($relation)
	{
		$count = $this->getFromModel($relation)->count();

		// Wrap in a link if possible
		$show = 'Admin\\' .ucfirst($relation). 'Controller@'.strtolower(get_class($this->object));
		if ($this->actionExists($show)) {
			$count = HTML::linkAction($show, $count, $this->object->id);
		}

		return $count;
	}

	/**
	 * Format a boolean value
	 *
	 * @param boolean $boolean
	 *
	 * @return string
	 */
	protected function boolean($boolean)
	{
		return $boolean ? 'Yes' : 'No';
	}
}
