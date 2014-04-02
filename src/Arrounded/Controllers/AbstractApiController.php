<?php
namespace Arrounded\Controllers;

use Arrounded\Abstracts\AbstractRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\Controller;
use Illuminate\Support\Contracts\ArrayableInterface;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
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
	 * Wray items and format responses
	 *
	 * @param ArrayableInterface $items
	 * @param integer            $statusCode
	 *
	 * @return Response
	 */
	public function wrap(ArrayableInterface $items, $statusCode = 200)
	{
		$meta = array();

		// If it's a single model, return it
		if ($items instanceof Model) {
			$items = new Collection([$items]);
		}

		// If we have a Paginator, split the data and metadata into two
		if ($items instanceof Paginator) {
			$meta  = $items->toArray();
			$items = array_pull($meta, 'data');
		}

		// Build response
		$response = $this->wrapWithResource($items);
		if ($meta) {
			$response['meta'] = $meta;
		}

		return Response::json($response, $statusCode);
	}

	/**
	 * Wrap entries with a resource
	 *
	 * @param Collection $items
	 * @param string     $resource
	 *
	 * @return array
	 */
	protected function wrapWithResource($items, $resource = null)
	{
		// Get and format resource
		if (!$resource) {
			$resource = $this->repository->getModel();
			$resource = snake_case($resource);
			$resource = Str::plural($resource);
		}

		// Unwrap any unwrapped object
		if (method_exists($items, 'toArray')) {
			$items = $items->toArray();
		}

		return array($resource => $items);
	}
}
