<?php
namespace Arrounded\Services\Statistics;

use Arrounded\Abstracts\AbstractModel;
use Arrounded\Assets\Chart;

abstract class AbstractStatistics
{
	/**
	 * @type array
	 */
	protected $graphs = [];

	/**
	 * @type AbstractModel
	 */
	private $model;

	/**
	 * @param AbstractModel $user
	 */
	public function __construct(AbstractModel $user)
	{
		$this->model = $user;
	}

	/**
	 * Add a graph to render
	 *
	 * @param string $name
	 * @param string $type
	 * @param array  $data
	 */
	public function add($name, $type, array $data)
	{
		$keys   = array_keys($data);
		$values = array_values($data);

		$this->graphs[$name] = Chart::make($type, $name)->setLabels($keys)->setDatasets([$values]);
	}

	/**
	 * @return array
	 */
	abstract public function compute();

	/**
	 * Render the graphs out
	 *
	 * @return string
	 */
	public function render()
	{
		return implode(PHP_EOL, $this->get());
	}
}
