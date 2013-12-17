<?php
namespace Arrounded\Controllers;

use Arrounded\Abstracts\AbstractSmartController;
use Input;
use Request;
use Response;

/**
 * A smart controller based on an Eloquent object
 */
abstract class SmartController extends AbstractSmartController
{
	/**
	 * An object of the model
	 *
	 * @var Eloquent
	 */
	protected $object;

	/**
	 * Build a new SmartController
	 */
	public function __construct()
	{
		parent::__construct();

		$this->object = $this->reflection->newModel();
	}

	////////////////////////////////////////////////////////////////////
	///////////////////////////// CORE METHODS /////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Returns a base index
	 *
	 * @param  array   $eager
	 * @param  integer $paginate
	 *
	 * @return View
	 */
	protected function coreIndex($eager = array(), $paginate = null)
	{
		$items = $this->object->with($eager);
		if ($paginate) {
			$paginator = $items->paginate($paginate);
			$items     = $paginator->getItems();
		} else {
			$items = $items->get();
		}

		return $this->getView('index', array(
			'items' => $items,
		));
	}

	/**
	 * Get the core create view
	 *
	 * @param  array  $data  Additional data
	 *
	 * @return View
	 */
	protected function coreCreate($data = array())
	{
		return $this->getView('edit', array_merge(
			$this->getFormData(),
			$data
		));
	}

	/**
	 * Get the core edit view
	 *
	 * @param  array  $data  Additional data
	 *
	 * @return View
	 */
	protected function coreEdit($item, $data = array())
	{
		$item = $this->object->findOrFail($item);
		$data['item'] = $item;
		$data = array_merge($this->getFormData($item), $data);

		return $this->getView('edit', $data);
	}

	/**
	 * Update an item
	 *
	 * @param  integer $item
	 *
	 * @return Redirect
	 */
	protected function coreUpdate($item)
	{
		// Get item
		$item  = $item ? $this->object->findOrFail($item) : clone $this->object;
		$input = Input::all();

		// Execute hooks
		$this->onUpdate($input, $item);

		// Update attributes (temporary)
		$item->fill($input)->save();

		return $this->getRedirect('index');
	}

	/**
	 * Delete an item
	 *
	 * @param  integer $item
	 *
	 * @return Redirect
	 */
	protected function coreDestroy($item)
	{
		$this->object->findOrFail($item)->delete();

		if (Request::ajax()) {
			return Response::json(array(
				'status' => 200,
			));
		}

		return $this->getRedirect('index');
	}
}