<?php
namespace Arrounded\Localization;

use Illuminate\Container\Container;
use Illuminate\Support\Str;

class Localizer
{
	/**
	 * The Container
	 *
	 * @var Container
	 */
	protected $app;

	/**
	 * The localization domain
	 *
	 * @var string
	 */
	protected $domain;

	/**
	 * The localization encoding
	 *
	 * @var string
	 */
	protected $encoding = 'UTF-8';

	/**
	 * Where translation files are located
	 *
	 * @var string
	 */
	protected $translations;

	/**
	 * The available locales
	 *
	 * @var array
	 */
	protected $locales = array(
		'de' => 'de_DE',
		'en' => 'en_US',
		'fr' => 'fr_FR',
	);

	/**
	 * Build a new Localizer
	 *
	 * @param Container $app
	 * @param string    $domain
	 * @param string    $translations
	 */
	public function __construct(Container $app, $domain, $translations)
	{
		$this->app          = $app;
		$this->domain       = $domain;
		$this->translations = $translations;
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// DOMAIN ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the translation domain
	 *
	 * @return string
	 */
	public function getDomain()
	{
		return $this->domain;
	}

	/**
	 * Get the folder where the translations reside
	 *
	 * @param string $subfolder
	 *
	 * @return string
	 */
	public function getTranslationsFolder($subfolder = null)
	{
		$subfolder = $subfolder ? '/'.$subfolder : $subfolder;

		return $this->translations.$subfolder;
	}

	/**
	 * Get the folder where a locale's translations reside
	 *
	 * @param string $locale
	 *
	 * @return string
	 */
	public function getLocaleFolder($locale)
	{
		$folder = sprintf('%s.%s/LC_MESSAGES', $locale, $this->getEncoding(true));

		return $this->getTranslationsFolder($folder);
	}

	/**
	 * Get the encoding
	 *
	 * @param boolean $slug
	 *
	 * @return string
	 */
	public function getEncoding($slug = false)
	{
		return $slug ? Str::slug($this->encoding, '') : $this->encoding;
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// LOCALES ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the current locale
	 *
	 * @return string
	 */
	public function getLocale()
	{
		return setLocale(LC_ALL, 0);
	}

	/**
	 * Set the application's locale
	 *
	 * @param string $locale
	 */
	public function setLocale($locale)
	{
		$this->app->setLocale($locale);

		// Translate language to locale (en => en_US)
		$locale = array_get($this->locales, $locale).'.'.$this->getEncoding(true);

		// Set locale
		putenv('LC_ALL='.$locale);
		setlocale(LC_ALL, $locale);

		// Specify the location of the translation tables
		bindtextdomain($this->domain, $this->translations);
		textdomain($this->domain);
	}

	/**
	 * Get the available locales
	 *
	 * @return array
	 */
	public function getLocales()
	{
		return $this->locales;
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Sanitize a picked locale versus the allowed ones
	 *
	 * @param string $locale
	 *
	 * @return string
	 */
	public function sanitizeLocale($locale = null)
	{
		$default = $this->app['config']['app.locale'];
		$allowed = array_keys($this->locales);
		$locale  = in_array($locale, $allowed) ? $locale : $default;

		return $locale;
	}
}
