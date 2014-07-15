<?php
namespace Arrounded\Assets;

use Illuminate\Console\Command;

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
		$views = new \RecursiveDirectoryIterator($views);
		!dd($views);
	}
}
