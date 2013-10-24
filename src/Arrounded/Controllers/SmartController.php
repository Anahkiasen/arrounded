<?php
namespace Arrounded\Controllers;

use Illuminate\Routing\Controllers\Controller;
use Input;
use Redirect;
use Request;
use Response;
use View;

/**
 * A base controller with smart capabilities
 */
abstract class SmartController extends Controller
{
	/**
	 * The ReflectionController instance
	 *
	 * @var ReflectionController
	 */
	protected $reflection;

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
		$this->reflection = new ReflectionController($this);
		$this->object     = $this->reflection->newModel();
	}

	////////////////////////////////////////////////////////////////////
	///////////////////////////////// CRUD /////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		return $this->coreIndex();
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		return $this->coreCreate();
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		return $this->coreUpdate();
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $item
	 * @return Response
	 */
	public function show($item)
	{
		return $this->coreShow($item);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $item
	 * @return Response
	 */
	public function edit($item)
	{
		return $this->coreEdit($item);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $item
	 * @return Response
	 */
	public function update($item)
	{
		return $this->coreUpdate($item);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $item
	 * @return Response
	 */
	public function destroy($item)
	{
		return $this->coreDestroy($item);
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// HOOKS /////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Execute actions on a model's updating
	 *
	 * @param  Eloquent $model
	 *
	 * @return void
	 */
	protected function onUpdate(array $input, $model)
	{
		// ...
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

	////////////////////////////////////////////////////////////////////
	////////////////////////////// VIEW DATA ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get data for the current form
	 *
	 * @return array
	 */
	protected function getFormData($item = null)
	{
		$route = $item ? 'update' : 'store';

		return array(
			'route' => $this->getRoute($route),
		);
	}

	////////////////////////////////////////////////////////////////////
	///////////////////////////// RELATED DATA /////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get a route
	 *
	 * @param  string $route
	 * @param  array  $parameters
	 *
	 * @return string
	 */
	protected function getRoute($route)
	{
		return sprintf('%s.%s', $this->reflection->resource(), $route);
	}

	/**
	 * Get a Redirect Response to a rute
	 *
	 * @param  string $route
	 *
	 * @return Redirect
	 */
	protected function getRedirect($route)
	{
		return Redirect::route($this->getRoute($route));
	}

	/**
	 * Get a view
	 *
	 * @param  string $view
	 * @param  array  $data
	 *
	 * @return View
	 */
	protected function getView($view, $data = array())
	{
		return View::make($this->reflection->resource().'.'.$view, $data);
	}
}