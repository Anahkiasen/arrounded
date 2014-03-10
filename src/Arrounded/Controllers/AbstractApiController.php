<?php
namespace Arrounded\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Contracts\ArrayableInterface;
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
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $item
	 * @return Response
	 */
	public function destroy($item)
	{
		$this->repository->delete($item);

		return Response::json([], 204);
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Call a repository method and wrap its response
	 *
	 * @param string $method
	 * @param array  $arguments...
	 *
	 * @return Response
	 */
	protected function wrapRepository()
	{
		$arguments = func_get_args();
		$method    = array_shift($arguments);
		$response  = call_user_func_array([$this->repository, $method], $arguments);

		return $this->wrap($response);
	}

	/**
	 * Wrap one or more items in an API convention format
	 *
	 * @param ArrayableInterface $items
	 *
	 * @return Response
	 */
	protected function wrap(ArrayableInterface $items)
	{
		return $items;
	}
}
