<?php
namespace Arrounded\Localization;

use Illuminate\Support\ServiceProvider;
use Twig_Extensions_Extension_I18n;
use Arrounded\Localization\Services\Compiler;
use Arrounded\Localization\Services\Extractor;

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

		$this->app->bind('i18n.compiler', function ($app) {
			return new Compiler($app);
		});

		$this->app->bind('i18n.extractor', function ($app) {
			return new Extractor($app);
		});
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
