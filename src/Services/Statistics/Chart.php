<?php
namespace Arrounded\Services\Statistics;

use Arrounded\Collection;

class Chart
{
	/**
	 * The element the graphic is for
	 *
	 * @var string
	 */
	protected $element;

	/**
	 * The type of the graphic
	 *
	 * @var string
	 */
	protected $type = 'Line';

	/**
	 * The datasets
	 *
	 * @var array
	 */
	protected $datasets = array();

	/**
	 * The graphics options
	 *
	 * @var array
	 */
	protected $options = array();

	/**
	 * The color scheme to use
	 *
	 * @var array
	 */
	protected $colors = array('#21323D', '#584A5E', '#7D4F6D', '#9D9B7F', '#C7604C', '#D97041');

	/**
	 * The various labels
	 *
	 * @var array
	 */
	protected $labels = array();

	/**
	 * Magic metod for constructor
	 *
	 * @param string $type
	 * @param string $element
	 *
	 * @return self
	 */
	public static function make($type, $element)
	{
		$chart = new static;
		$chart->setType($type);
		$chart->setElement($element);

		return $chart;
	}

	/**
	 * Render on string cast
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->render();
	}

	////////////////////////////////////////////////////////////////////
	///////////////////////// GETTERS AND SETTERS //////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Set the color scheme to use
	 *
	 * @param array $colors
	 *
	 * @return $this
	 */
	public function setColors(array $colors)
	{
		$this->colors = $colors;

		return $this;
	}

	/**
	 * Sets the element the graphic is for
	 *
	 * @param string $element the element
	 *
	 * @return self
	 */
	public function setElement($element)
	{
		$this->element = 'statistic--'.$element;

		return $this;
	}

	/**
	 * Sets the type of the graphic
	 *
	 * @param string $type the type
	 *
	 * @return self
	 */
	public function setType($type)
	{
		$this->type = $type;

		return $this;
	}

	/**
	 * Sets the datasets
	 *
	 * @param array|Collection $datasets the datasets
	 *
	 * @return self
	 */
	public function setDatasets($datasets)
	{
		$this->datasets = $this->formatDatasets($datasets);

		return $this;
	}

	/**
	 * Sets the graphics options
	 *
	 * @param array $options the options
	 *
	 * @return self
	 */
	public function setOptions(array $options)
	{
		$this->options = $options;

		return $this;
	}

	/**
	 * Sets the various labels
	 *
	 * @param array $labels the labels
	 *
	 * @return self
	 */
	public function setLabels(array $labels)
	{
		$this->labels = $labels;

		return $this;
	}

	////////////////////////////////////////////////////////////////////
	////////////////////////////// RENDERING ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Render the chart
	 *
	 * @return string
	 */
	public function render()
	{
		$template = 'new Chart(document.getElementById("%s").getContext("2d")).%s(%s, %s);';
		$template = sprintf(
			$template,
			$this->element,
			$this->type,
			json_encode($this->datasets),
			json_encode($this->options)
		);

		return $template;
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Format datasets
	 *
	 * @param array|Collection $datasets
	 *
	 * @return array
	 */
	protected function formatDatasets($datasets)
	{
		$data = [];
		switch ($this->type) {

			case 'Pie':
			case 'Doughnut':
				foreach ($datasets as $key => $value) {
					$data[] = array(
						'value' => $value,
						'color' => array_get($this->colors, $key),
					);
				}
				break;

			case 'Bar':
				$data['labels']   = $this->labels;
				$data['datasets'] = array();
				foreach ($datasets as $key => $value) {
					$data['datasets'][] = array(
						'fillColor'   => array_get($this->colors, $key),
						'strokeColor' => array_get($this->colors, $key),
						'data'        => $value,
					);
				}
				break;
		}

		return $data;
	}
}
