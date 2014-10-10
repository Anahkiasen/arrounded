<?php
namespace Arrounded\Services\Statistics;

use Arrounded\Abstracts\AbstractModel;
use Arrounded\Collection;
use Closure;

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
	 * @type array
	 */
	protected $labels = [];

	/**
	 * The default chart options
	 *
	 * @type array
	 */
	protected $options = [];

	/**
	 * Cached results of the datasets
	 *
	 * @type array
	 */
	protected $results = [];

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

	/**
	 * Execute a closure on a set of results
	 *
	 * @param string  $dataset
	 * @param Closure $callback
	 * @param array   $keys
	 *
	 * @return array
	 */
	public function on($dataset, Closure $callback, $keys = array())
	{
		// Cache result
		if (!isset($this->results[$dataset])) {
			$this->results[$dataset] = $this->$dataset->get();
		}

		// Get and format results
		$results = $callback(clone $this->results[$dataset]);
		$results = $this->formatResults($results, $keys);

		return $results;
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
	 * @return array
	 */
	public function getLabels()
	{
		return $this->labels;
	}

	/**
	 * Compute from a passed array
	 *
	 * @param array $compute
	 *
	 * @return array
	 */
	protected function computeFrom(array $compute = array())
	{
		foreach ($compute as $type => $graphs) {
			foreach ($graphs as $name => $method) {
				$this->addGraph($name, $type, $this->formatResults($method));
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
	///////////////////////////// FORMATTING /////////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * Fill the gaps in an array
	 *
	 * @param array  $results
	 * @param string $keys
	 *
	 * @return array
	 */
	protected function fillGaps(array $results, $keys)
	{
		$filler = array_fill(0, count($keys), 0);
		$filler = array_combine($keys, $filler);

		return array_merge($filler, $results);
	}

	/**
	 * Format results to usable array
	 *
	 * @param string|array $method
	 * @param array        $keys
	 *
	 * @return array
	 */
	protected function formatResults($method, $keys = array())
	{
		$result = is_string($method) ? $this->$method() : $method;
		$result = $result instanceof Collection ? $result->toArray() : $result;

		// Add keys
		if ($keys) {
			$keys   = array_intersect_key($keys, $result);
			$result = array_combine($keys, $result);
		}

		return $result;
	}

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
