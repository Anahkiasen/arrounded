<?php
namespace Arrounded\Services;

use Illuminate\Support\Facades\URL;

/**
 * Generates and formats metadata
 */
class Metadata
{
	/**
	 * @type string
	 */
	protected $project = 'website';

	/**
	 * @type array
	 */
	protected $defaults = [];

	/**
	 * @param string $project
	 */
	public function setProject($project)
	{
		$this->project = $project;
	}

	/**
	 * @param array $defaults
	 */
	public function setDefaults($defaults)
	{
		$this->defaults = $defaults;
	}

	/**
	 * Renders the metadata
	 *
	 * @param array $attributes
	 *
	 * @return string
	 */
	public function render(array $attributes = array())
	{
		$html = '';

		// Add some default options
		$attributes = array_merge(array(
			'card' => 'summary',
			'site' => $this->project,
			'url'  => URL::current(),
		), $this->defaults, $attributes);

		// Format URLs if provided
		$image = array_get($attributes, 'image');
		if (!file_exists($image) or strpos($image, 'placeholder') !== false) {
			$image = $this->getPlaceholderIllustration();
		}
		$attributes['image'] = URL::asset($image);

		// Get Twitter equivalents
		$twitterProperties = array(
			'name'  => 'title',
			'image' => 'image:src',
		);

		// Append attributes
		foreach ($attributes as $name => $value) {
			$twitter = array_get($twitterProperties, $name, $name);
			$html .= sprintf('<meta name="twitter:%s" property="og:%s" content="%s">', $twitter, $name, $value).PHP_EOL;
		}

		return $html;
	}

	/**
	 * @return string
	 */
	protected function getPlaceholderIllustration()
	{
		return 'app/img/logo.png';
	}
}
