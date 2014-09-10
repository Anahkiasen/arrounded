<?php
namespace Arrounded\Controllers;

use Arrounded\Abstracts\AbstractRepository;
use Illuminate\Routing\Controller;
use Input;
use Response;

abstract class AbstractApiController extends Controller
{
	/**
	 * The repository in use
	 *
	 * @var AbstractRepository
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
	 * @return Response
	 */
	public function index()
	{
		return $this->repository->getPaginated();
	}

	/**
	 * Create a new resource
	 *
	 * @return Response
	 */
	public function store()
	{
		return $this->update();
	}

	/**
	 * Update an existing resource
	 *
	 * @param integer $item
	 *
	 * @return Response
	 */
	public function update($item = null)
	{
		$attributes = Input::all();
		$item       = $this->repository->validate($attributes, $item);

		// Cancel if invalid input
		if ($errors = $item->getErrors()) {
			$exception = sprintf('Dingo\Api\Exception\%sResourceFailedException', ucfirst(__FUNCTION__));
			$message   = sprintf('Could not %s %s', __FUNCTION__, $item->getClass());

			throw new $exception($message, $errors);
		}

		return $this->show($item->id);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int $item
	 *
	 * @return Response
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
	 * @return Response
	 */
	public function destroy($item)
	{
		$this->repository->delete($item);

		return Response::json([], 204);
	}
}
