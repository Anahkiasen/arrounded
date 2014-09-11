<?php
namespace Arrounded\Services\Statistics;

use Arrounded\Abstracts\AbstractModel;

abstract class AbstractStatistics
{
	/**
	 * @type array
	 */
	protected $graphs = [];

	/**
	 * The default chart options
	 *
	 * @type array
	 */
	protected $options = [];

	/**
	 * @type AbstractModel
	 */
	protected $model;

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

		$this->graphs[$name] = Chart::make($type, $name)
		                            ->setOptions($this->options)
		                            ->setLabels($keys)
		                            ->setDatasets([$values]);
	}

	/**
	 * @return void
	 */
	abstract public function compute();

	/**
	 * Return the computed graphs
	 *
	 * @return array
	 */
	public function get()
	{
		$this->compute();

		return $this->graphs;
	}

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
