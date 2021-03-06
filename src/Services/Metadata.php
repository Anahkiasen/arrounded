<?php
namespace Arrounded\Services;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\URL;
use League\Csv\Reader;

/**
 * Generates and formats metadata.
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
     * Set the metadata from a file.
     *
     * @param string $file
     */
    public function setDefaultsFromFile($file)
    {
        $file = new Reader($file);

        // Fetch columns
        $rows = $file->fetchOne();
        $file->setOffset(1);

        // Fetch entries and set defaults
        $entries = $file->fetchAssoc($rows);
        foreach ($entries as $entry) {
            if (strpos(URL::current(), $entry['url']) !== false) {
                $this->defaults = $entry;
            }
        }
    }

    /**
     * @param array $unwrapped
     */
    public function setUnwrapped($unwrapped)
    {
        $this->unwrapped = array_merge(['title', 'keywords', 'description'], $unwrapped);
    }

    /**
     * Renders the metadata.
     *
     * @param array $attributes
     *
     * @return string
     */
    public function render(array $attributes = [])
    {
        $html = '';

        // Add some default options
        $attributes = array_merge([
            'card' => 'summary',
            'site' => $this->project,
            'url'  => $this->app['url']->current(),
        ], $this->defaults, $attributes);

        // Format URLs if provided
        $image = array_get($attributes, 'image');
        if (!file_exists($this->app['path.public'].$image) || strpos($image, 'placeholder') !== false) {
            $image = $this->getPlaceholderIllustration();
        }
        $attributes['image'] = $this->app['url']->asset($image);

        // Get Twitter equivalents
        $twitterProperties = [
            'name'  => 'title',
            'image' => 'image:src',
        ];

        // Append attributes
        foreach ($attributes as $name => $value) {
            $twitter = array_get($twitterProperties, $name, $name);
            $html .= $this->getWrapper($twitter, $name, $value).PHP_EOL;
        }

        return $html;
    }

    /**
     * Get the correct HTML wrapper.
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
            return sprintf('<meta name="%s" content="%s">', $name, $value);
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
