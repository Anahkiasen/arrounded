<?php
namespace Arrounded\Abstracts\Controllers;

use Arrounded\Abstracts\Eloquent;
use Arrounded\Abstracts\Validator;
use Arrounded\Traits\Redirectable;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

/**
 * A base controller with smart capabilities.
 */
abstract class AbstractSmartController extends Controller
{
    use Redirectable;

    /**
     * The ReflectionController instance.
     *
     * @type ReflectionController
     */
    protected $reflection;

    /**
     * Build a new SmartController.
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
     * @param int $item
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
     * @param int $item
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
     * @param int $item
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
     * @param int $item
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
     * Execute actions on a model's updating.
     *
     * @param array    $input
     * @param Eloquent $model
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
     * @param array    $eager
     * @param int|null $paginate
     *
     * @return \Illuminate\View\View
     */
    abstract protected function coreIndex($eager = [], $paginate = null);

    /**
     * Get the core create view.
     *
     * @param array $data Additional data
     *
     * @return \Illuminate\View\View
     */
    abstract protected function coreCreate($data = []);

    /**
     * Display the specified resource.
     *
     * @param int $user
     *
     * @return \Illuminate\View\View
     */
    abstract protected function coreShow($user);

    /**
     * Get the core edit view.
     *
     * @param int   $item
     * @param array $data Additional data
     *
     * @return \Illuminate\View\View
     */
    abstract protected function coreEdit($item, $data = []);

    /**
     * Update an item.
     *
     * @param int|null $item
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    abstract protected function coreUpdate($item = null);

    /**
     * Delete an item.
     *
     * @param int $item
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    abstract protected function coreDestroy($item);

    ////////////////////////////////////////////////////////////////////
    ////////////////////////////// VIEW DATA ///////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Get data for the current form.
     *
     * @param array $data
     *
     * @return array
     */
    protected function getFormData(array $data = [])
    {
        $route = array_get($data, 'item') ? 'update' : 'store';

        return array_merge([
            'route' => $this->getRoute($route),
        ], $data);
    }

    /**
     * Get the data to display.
     *
     * @param int $item
     *
     * @return array
     */
    abstract protected function getShowData($item);

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// FILTERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Validate the ownership of a route.
     *
     * @param Route        $route
     * @param string       $parameter
     * @param string|array $fields
     *
     * @return \Illuminate\Http\RedirectResponse|null
     */
    protected function validateOwnership($route, $parameter, $fields = 'user_id')
    {
        $fields = (array) $fields;

        if (Auth::check()) {
            $user = Auth::user();

            // Gather fields
            $model = $route->getParameter($parameter);
            if (!$model) {
                return;
            }

            foreach ($fields as $key => $field) {
                $fields[$key] = $model->$field;
            }

            // Validate ownership
            if ($model and !in_array($user->id, $fields)) {
                return Redirect::home();
            }
        }
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// RELATED ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get a route.
     *
     * @param string $route
     *
     * @return string
     */
    protected function getRoute($route)
    {
        return sprintf('%s.%s', $this->reflection->resource(), $route);
    }

    /**
     * Get an URL.
     *
     * @param string $route
     * @param array  $parameters
     *
     * @return string
     */
    protected function getPath($route, $parameters = [])
    {
        return URL::action(get_class($this).'@'.$route, $parameters);
    }

    /**
     * Get a Redirect Response to a rute.
     *
     * @param string $route
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function getRedirect($route)
    {
        return Redirect::route($this->getRoute($route));
    }

    /**
     * Get a view.
     *
     * @param string $view
     * @param array  $data
     *
     * @return \Illuminate\View\View
     */
    protected function getView($view, $data = [])
    {
        return View::make($this->reflection->resource().'.'.$view, $data);
    }
}
