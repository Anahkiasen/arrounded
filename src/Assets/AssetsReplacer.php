<?php
namespace Arrounded\Assets;

use Illuminate\Console\Command;
use Symfony\Component\Finder\Finder;

class AssetsReplacer extends Command
{
    /**
     * The console command name.
     *
     * @type string
     */
    protected $name = 'assets:replace';

    /**
     * The console command description.
     *
     * @type string
     */
    protected $description = 'Replace calls to assets collections in the files with their minified version';

    /**
     * @type AssetsHandler
     */
    protected $handler;

    /**
     * @param AssetsHandler $handler
     */
    public function __construct(AssetsHandler $handler)
    {
        parent::__construct();

        $this->handler = $handler;
    }

    /**
     * Execute the command
     */
    public function fire()
    {
        $views = app_path('views');

        // List all views
        $finder = new Finder();
        $views  = $finder->files()->in($views)->getIterator();
        $views  = array_keys(iterator_to_array($views));

        // Replace in views
        $matcher = '/{{ ?Assets\.(styles|scripts)\(["\'](.+)["\']\)(\|raw)? ?}}/';
        foreach ($views as $view) {
            $this->comment('Replacing calls in '.basename($view));
            $contents = file_get_contents($view);
            $contents = preg_replace_callback($matcher, [$this, 'replaceAssetsCalls'], $contents);
            file_put_contents($view, $contents);
        }
    }

    /**
     * Replace Assets calls in views
     *
     * @param array $matches
     *
     * @return string
     */
    protected function replaceAssetsCalls($matches)
    {
        list(, $type, $container) = $matches;

        return $this->handler->$type($container);
    }
}
