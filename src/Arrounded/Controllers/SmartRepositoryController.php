<?php
namespace Arrounded\Controllers;

use Arrounded\Abstracts\AbstractSmartController;
use Arrounded\Interfaces\RepositoryInterface;
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
		return $this->getView('edit', array_merge(
			$this->getFormData(),
			$data
		));
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
		return $this->getView('show', array(
			'item' => $this->repository->find($user),
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
		$item = $this->repository->find($item);

		return $this->getView('edit', array_merge(
			$this->getFormData($item),
			$data,
			array('item' => $item)
		));
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
		$item  = $item ? $this->repository->find($item) : $this->repository->items()->newInstance();
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
		$this->repository->delete($item);

		if (Request::ajax()) {
			return Response::json(array(
				'status' => 200,
			));
		}

		return $this->getRedirect('index');
	}
}