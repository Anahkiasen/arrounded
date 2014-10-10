<?php
namespace Arrounded\Abstracts\Controllers;

use Arrounded\Abstracts\Eloquent;
use Arrounded\Abstracts\Validator;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;

/**
 * A base controller with smart capabilities
 */
abstract class AbstractSmartController extends Controller
{
	/**
	 * The ReflectionController instance
	 *
	 * @type ReflectionController
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
	 * @return \Illuminate\View\View
	 */
	public function index()
	{
		return $this->coreIndex();
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\View\View
	 */
	public function create()
	{
		return $this->coreCreate();
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function store()
	{
		return $this->coreUpdate();
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int $item
	 *
	 * @return \Illuminate\View\View
	 */
	public function show($item)
	{
		return $this->coreShow($item);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int $item
	 *
	 * @return \Illuminate\View\View
	 */
	public function edit($item)
	{
		return $this->coreEdit($item);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int $item
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function update($item)
	{
		return $this->coreUpdate($item);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int $item
	 *
	 * @return \Illuminate\Http\RedirectResponse
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
	 * @param  array    $input
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
	 * @param  array        $eager
	 * @param  integer|null $paginate
	 *
	 * @return \Illuminate\View\View
	 */
	abstract protected function coreIndex($eager = array(), $paginate = null);

	/**
	 * Get the core create view
	 *
	 * @param  array $data Additional data
	 *
	 * @return \Illuminate\View\View
	 */
	abstract protected function coreCreate($data = array());

	/**
	 * Display the specified resource.
	 *
	 * @param  int $user
	 *
	 * @return \Illuminate\View\View
	 */
	abstract protected function coreShow($user);

	/**
	 * Get the core edit view
	 *
	 * @param  integer $item
	 * @param  array   $data Additional data
	 *
	 * @return \Illuminate\View\View
	 */
	abstract protected function coreEdit($item, $data = array());

	/**
	 * Update an item
	 *
	 * @param  integer|null $item
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	abstract protected function coreUpdate($item = null);

	/**
	 * Delete an item
	 *
	 * @param  integer $item
	 *
	 * @return \Illuminate\Http\RedirectResponse
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
	 * @return \Illuminate\Http\RedirectResponse
	 */
	protected function redirectHere($action, $parameters = array())
	{
		$controller = get_class($this);

		return Redirect::action($controller.'@'.$action, $parameters);
	}

	/**
	 * Create a redirect for a failed validation
	 *
	 * @param Validator $validation
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	protected function redirectFailedValidation($validation)
	{
		return Redirect::back()->withInput()->withErrors($validation);
	}

	/**
	 * Get a route
	 *
	 * @param  string $route
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
	 * @return \Illuminate\Http\RedirectResponse
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
	 * @return \Illuminate\View\View
	 */
	protected function getView($view, $data = array())
	{
		return View::make($this->reflection->resource().'.'.$view, $data);
	}
}
