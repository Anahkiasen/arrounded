<?php
namespace Arrounded\Abstracts\Controllers;

use Arrounded\Abstracts\AbstractRepository;
use Illuminate\Routing\Controller;
use Input;
use Response;

abstract class AbstractApiController extends Controller
{
    /**
     * The repository in use
     *
     * @type AbstractRepository
     */
    protected $repository;

    /**
     * Build a new Controller
     *
     * @param AbstractRepository $repository
     */
    public function __construct(AbstractRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Pagination\Paginator
     */
    public function index()
    {
        return $this->repository->getPaginated();
    }

    /**
     * Create a new resource
     *
     * @return \Arrounded\Abstracts\Models\AbstractModel
     */
    public function store()
    {
        return $this->update();
    }

    /**
     * Update an existing resource
     *
     * @param integer|null $item
     *
     * @return \Arrounded\Abstracts\Models\AbstractModel
     */
    public function update($item = null)
    {
        $attributes = Input::all();
        $item       = $item ? $this->repository->update($item, $attributes) : $this->repository->create($attributes);

        return $this->show($item->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $item
     *
     * @return \Arrounded\Abstracts\Models\AbstractModel
     */
    public function show($item)
    {
        return $this->repository->find($item);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $item
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($item)
    {
        $this->repository->delete($item);

        return Response::json([], 204);
    }
}
