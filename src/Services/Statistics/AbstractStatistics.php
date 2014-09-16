<?php
namespace Arrounded\Services\Statistics;

use Arrounded\Abstracts\AbstractModel;
use Arrounded\Collection;

/**
 * Computes and renders statistics based on datasets
 *
 * @author Maxime Fabre <ehtnam6@gmail.com>
 */
abstract class AbstractStatistics extends Collection
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

	//////////////////////////////////////////////////////////////////////
	////////////////////////////// DATASETS //////////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * @param string $name
	 *
	 * @return AbstractModel|Collection
	 */
	public function __get($name)
	{
		return $this->items[$name];
	}

	/**
	 * @param string $name
	 * @param mixed  $value
	 */
	public function __set($name, $value)
	{
		$this->items[$name] = $value;
	}

	//////////////////////////////////////////////////////////////////////
	/////////////////////////////// GRAPHS ///////////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * Add a graph to render
	 *
	 * @param string $name
	 * @param string $type
	 * @param array  $data
	 */
	public function addGraph($name, $type, array $data)
	{
		$keys   = array_keys($data);
		$values = array_values($data);
		$chart  = Chart::make($type, $name)
		               ->setOptions($this->options)
		               ->setLabels($keys)
		               ->setDatasets([$values]);

		$this->graphs[$name] = $chart;
	}

	/**
	 * Return the computed graphs
	 *
	 * @return array
	 */
	public function getGraphs()
	{
		$this->compute();

		return $this->graphs;
	}

	/**
	 * Compute from a passed array
	 *
	 * @param array $compute
	 */
	protected function computeFrom(array $compute = array())
	{
		foreach ($compute as $type => $graphs) {
			foreach ($graphs as $name => $method) {
				$this->addGraph($name, $type, $this->$method());
			}
		}

		return $this->graphs;
	}

	/**
	 * Compute the statistics
	 *
	 * @return void
	 */
	abstract public function compute();

	//////////////////////////////////////////////////////////////////////
	////////////////////////////// RENDERING /////////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * Render the graphs out
	 *
	 * @return string
	 */
	public function render()
	{
		return implode(PHP_EOL, $this->getGraphs());
	}
}
