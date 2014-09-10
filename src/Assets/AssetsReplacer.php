<?php
namespace Arrounded\Assets;

use Illuminate\Console\Command;
use Symfony\Component\Finder\Finder;

class AssetsReplacer extends Command
{

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'assets:replace';

	/**
	 * The console command description.
	 *
	 * @var string
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
		$finder = new Finder($views);
		$views  = $finder->files()->in($views)->getIterator();
		$views  = array_keys(iterator_to_array($views));

		// Replace in views
		foreach ($views as $view) {
			$this->comment('Replacing calls in '.basename($view));
			$contents = file_get_contents($view);
			$contents = preg_replace_callback('/{{ ?Assets\.(styles|scripts)\(["\'](.+)["\']\)(\|raw)? ?}}/', [$this, 'replaceAssetsCalls'], $contents);
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
		list (, $type, $container) = $matches;

		return $this->handler->$type($container);
	}
}
