<?php
namespace Arrounded\Services;

use Illuminate\Container\Container;

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
	 * @type array
	 */
	protected $unwrapped = ['title', 'keywords', 'description'];

	/**
	 * @type Container
	 */
	protected $app;

	/**
	 * @param Container $app
	 */
	public function __construct(Container $app)
	{
		$this->app = $app;
	}

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
	 * @param array $unwrapped
	 */
	public function setUnwrapped($unwrapped)
	{
		$this->unwrapped = array_merge(['title', 'keywords', 'description'], $unwrapped);
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
			'url'  => $this->app['url']->current(),
		), $this->defaults, $attributes);

		// Format URLs if provided
		$image = array_get($attributes, 'image');
		if (!file_exists($this->app['path.public'].$image) or strpos($image, 'placeholder') !== false) {
			$image = $this->getPlaceholderIllustration();
		}
		$attributes['image'] = $this->app['url']->asset($image);

		// Get Twitter equivalents
		$twitterProperties = array(
			'name'  => 'title',
			'image' => 'image:src',
		);

		// Append attributes
		foreach ($attributes as $name => $value) {
			$twitter = array_get($twitterProperties, $name, $name);
			$html .= $this->getWrapper($twitter, $name, $value).PHP_EOL;
		}

		return $html;
	}

	/**
	 * Get the correct HTML wrapper
	 *
	 * @param string $twitter
	 * @param string $name
	 * @param string $value
	 *
	 * @return string
	 */
	protected function getWrapper($twitter, $name, $value)
	{
		if (in_array($name, $this->unwrapped)) {
			return sprintf('<meta name="%s" contents="%s">', $name, $value);
		}

		return sprintf('<meta name="twitter:%s" property="og:%s" content="%s">', $twitter, $name, $value);
	}

	/**
	 * @return string
	 */
	protected function getPlaceholderIllustration()
	{
		return 'app/img/logo.png';
	}
}
