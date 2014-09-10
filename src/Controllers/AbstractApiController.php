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
