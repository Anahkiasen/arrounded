<?php
namespace Arrounded\Abstracts;

use Illuminate\Console\Command;

abstract class AbstractCommand extends Command
{
	/**
	 * Execute something for all locales
	 *
	 * @param Callable $closure
	 *
	 * @return void
	 */
	protected function forLocales(Callable $closure)
	{
		$locales = $this->laravel['i18n.localizer']->getLocales();
		foreach ($locales as $locale) {
			$closure($locale);
		}
	}
}
