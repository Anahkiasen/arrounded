<?php
namespace Arrounded\Abstracts;

use Illuminate\Routing\Controllers\Controller;
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