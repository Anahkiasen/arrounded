<?php
namespace Arrounded\Controllers;

use Arrounded\Abstracts\AbstractSmartController;
use Arrounded\Interfaces\RepositoryInterface;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Input;
use Request;
use Response;

/**
 * A smart controller based on a Repository implementation
 */
abstract class SmartRepositoryController extends AbstractSmartController
{
	/**
	 * The repository in use
	 *
	 * @var AbstractRepository
	 */
	protected $repository;

	/**
	 * The relationships to eager load automatically
	 *
	 * @var array
	 */
	protected $eagerLoaded = array();

	/**
	 * Build a new SmartRepositoryController
	 *
	 * @param RepositoryInterface $repository
	 */
	public function __construct(RepositoryInterface $repository)
	{
		parent::__construct();

		$this->repository = $repository;
	}

	////////////////////////////////////////////////////////////////////
	///////////////////////////////// CRUD /////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	protected function coreIndex($eager = array(), $paginate = null)
	{
		$eager = array_merge($this->eagerLoaded, $eager);

		return $this->getView('index', array(
			'items' => $this->repository->all($paginate)->load($eager),
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
		return $this->getView('edit', $this->getFormData($data));
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $user
	 *
	 * @return Response
	 */
	protected function coreShow($user)
	{
		return $this->getView('show', $this->getShowData($user));
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
		$item = $this->repository->find($item);
		$data['item'] = $item;

		return $this->getView('edit', $this->getFormData($data));
	}

	/**
	 * Update an item
	 *
	 * @param  integer $item
	 *
	 * @return Redirect
	 */
	protected function coreUpdate($item = null)
	{
		// Get item
		$item  = $item ? $this->repository->find($item) : $this->repository->items()->newInstance();
		$input = Input::all();

		// Execute hooks
		$this->onUpdate($input, $item);

		// Update attributes
		$item = $this->repository->update($item, $input);

		// Update relationships
		foreach ($input as $key => $value) {
			if (method_exists($item, $key) and $item->$key() instanceof BelongsToMany) {
				$item->$key()->sync($value);
			}
		}

		return $this->getRedirect('index')->with('success', true);
	}

	/**
	 * Delete an item
	 *
	 * @param  integer $item
	 * @param  boolean $force
	 *
	 * @return Redirect
	 */
	protected function coreDestroy($item, $force = false)
	{
		$this->repository->delete($item, $force);

		if (Request::ajax()) {
			return Response::json(array(), 204);
		}

		return $this->getRedirect('index');
	}

	////////////////////////////////////////////////////////////////////
	////////////////////////////// VIEW DATA ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the data to display
	 *
	 * @param integer $item
	 *
	 * @return array
	 */
	protected function getShowData($item)
	{
		// Get item from database
		$item = $this->repository->find($item);

		return array(
			'item' => $item,
		);
	}
}
