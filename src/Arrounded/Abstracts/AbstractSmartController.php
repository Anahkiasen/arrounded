<?php
namespace Arrounded\Abstracts;

use Arrounded\Controllers\ReflectionController;
use Controller;
use Redirect;
use View;

/**
 * A base controller with smart capabilities
 */
abstract class AbstractSmartController extends Controller
{
	/**
	 * The ReflectionController instance
	 *
	 * @var ReflectionController
	 */
	protected $reflection;

	/**
	 * Build a new SmartController
	 */
	public function __construct()
	{
		$this->reflection = new ReflectionController($this);
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
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	abstract protected function coreIndex($eager = array(), $paginate = null);

	/**
	 * Get the core create view
	 *
	 * @param  array  $data  Additional data
	 *
	 * @return View
	 */
	abstract protected function coreCreate($data = array());

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $user
	 *
	 * @return Response
	 */
	abstract protected function coreShow($user);

	/**
	 * Get the core edit view
	 *
	 * @param  array  $data  Additional data
	 *
	 * @return View
	 */
	abstract protected function coreEdit($item, $data = array());

	/**
	 * Update an item
	 *
	 * @param  integer $item
	 *
	 * @return Redirect
	 */
	abstract protected function coreUpdate($item = null);

	/**
	 * Delete an item
	 *
	 * @param  integer $item
	 *
	 * @return Redirect
	 */
	abstract protected function coreDestroy($item);

	////////////////////////////////////////////////////////////////////
	////////////////////////////// VIEW DATA ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get data for the current form
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	protected function getFormData(array $data = array())
	{
		$route = array_get($data, 'item') ? 'update' : 'store';

		return array_merge(array(
			'route' => $this->getRoute($route),
		), $data);
	}

	/**
	 * Get the data to display
	 *
	 * @param integer $item
	 *
	 * @return array
	 */
	abstract protected function getShowData($item);

	////////////////////////////////////////////////////////////////////
	///////////////////////////// RELATED DATA /////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Redirect to an action in the current controller
	 *
	 * @param string $action
	 * @param array  $parameters
	 *
	 * @return Redirect
	 */
	protected function redirectHere($action, $parameters = array())
	{
		$controller = get_class($this);

		return Redirect::action($controller.'@'.$action, $parameters);
	}

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
