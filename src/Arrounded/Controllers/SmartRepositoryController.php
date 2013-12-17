<?php
namespace Arrounded\Controllers;

use Arrounded\Interfaces\RepositoryInterface;
use Input;
use Request;
use Response;

/**
 * A smart controller based on a Repository implementation
 */
abstract class SmartRepositoryController extends AbstractSmartController
{
	/**
	 * The repository in use
	 *
	 * @var AbstractRepository
	 */
	protected $repository;

	/**
	 * Build a new SmartRepositoryController
	 *
	 * @param RepositoryInterface $repository
	 */
	public function __construct(RepositoryInterface $repository)
	{
		parent::__construct();

		$this->repository = $repository;
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
		return $this->getView('index', array(
			'items' => $this->repository->all(),
		));
	}
}