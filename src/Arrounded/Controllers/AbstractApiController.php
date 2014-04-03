<?php
namespace Arrounded\Controllers;

use Arrounded\Abstracts\AbstractRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Contracts\ArrayableInterface;
use Illuminate\Support\Str;
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
	/////////////////////////////// WRAPPERS ///////////////////////////
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
	 * Wrap a single model
	 *
	 * @param Model   $model
	 * @param integer $statusCode
	 *
	 * @return mixed
	 */
	protected function wrapSingleModel(Model $model, $statusCode = 200)
	{
		return new Collection([$model]);
	}

	/**
	 * Wray items and format responses
	 *
	 * @param ArrayableInterface $items
	 * @param integer            $statusCode
	 *
	 * @return Response
	 */
	public function wrap($items, $statusCode = 200)
	{
		$meta = array();

		// If it's a single model, return it
		if ($items instanceof Model) {
			$items = $this->wrapSingleModel($items);
			if ($items instanceof Response or $items instanceof JsonResponse) {
				return $items;
			}
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
		$resource = $resource ?: $this->infereResourceFromResponse($items);

		// Unwrap any unwrapped object
		if (method_exists($items, 'toArray')) {
			$items = $items->toArray();
		}

		return array($resource => $items);
	}

	/**
	 * Infere the resource to wrap items with
	 *
	 * @param ArrayableInterface $items
	 *
	 * @return string
	 */
	protected function infereResourceFromResponse($items)
	{
		if (method_exists($items, 'first')) {
			$first = $items->first();
		} else {
			$first = (array) $items;
			$first = head($first);
		}

		$resource = is_object($first) ? get_class($first) : $this->repository->getModel();
		$resource = snake_case($resource);
		$resource = Str::plural($resource);

		return $resource;
	}
}
