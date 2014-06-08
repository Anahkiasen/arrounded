<?php
namespace Arrounded\Localization\Commands;

use Arrounded\Abstracts\AbstractCommand;
use Illuminate\Console\Command;

class CompileTranslations extends AbstractCommand
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'lang:compile';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Compile translations';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$compiler = $this->laravel['i18n.compiler'];
		$compiler->setCommand($this);

		$this->forLocales([$compiler, 'compileLocale']);
	}
}
