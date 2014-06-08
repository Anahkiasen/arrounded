<?php
namespace Arrounded\Localization\Services;

/**
 * Compiles existing PO files to MO
 */
class Compiler extends AbstractService
{
	/**
	 * Generate the files for a locale
	 *
	 * @param string $locale
	 * @param array  $files
	 *
	 * @return void
	 */
	public function compileLocale($locale)
	{
		// Get output directory and file
		$translated = $this->app['i18n.extractor']->getLocaleFile($locale);

		// Recompile MO files
		$compiled = str_replace('po', 'mo', $translated);
		$this->execf('msgfmt %s -o %s', $translated, $compiled);

		// Print success
		if ($this->command) {
			$this->command->line('Compiled <info>'.$compiled.'</info>');
		}
	}
}
