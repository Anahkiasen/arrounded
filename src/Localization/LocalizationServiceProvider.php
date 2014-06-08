<?php
namespace Arrounded\Localization;

use Illuminate\Support\ServiceProvider;
use Twig_Extensions_Extension_I18n;

/**
 * Register the Localization classes
 */
class LocalizationServiceProvider extends ServiceProvider
{
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bind('i18n.localizer', function ($app) {
			return new Localizer($app, 'weholi', app_path('lang'));
		});

		$this->app->bind('i18n.compiler', 'Arrounded\Localization\Services\Compiler');
		$this->app->bind('i18n.extractor', 'Arrounded\Localization\Services\Extractor');
		$this->app->bind('i18n.extract', 'Arrounded\Localization\Commands\ExtractTranslations');
		$this->app->bind('i18n.compile', 'Arrounded\Localization\Commands\CompileTranslations');

		$this->commands(['i18n.extract', 'i18n.compile']);
	}

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$locale = $this->app['i18n.localizer']->sanitizeLocale();

		// Set the Laravel locale to user's
		if ($this->app['auth']->check()) {
			$locale = $this->app['auth']->user()->locale;
			$locale = $this->app['i18n.localizer']->sanitizeLocale($locale);
		}

		// Configure gettext
		$this->app['i18n.localizer']->setLocale($locale);

		// Add i18n Twig extension
		$this->app['twig']->addExtension(new Twig_Extensions_Extension_I18n);
	}
}
