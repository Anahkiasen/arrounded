<?php
namespace Arrounded\Traits;

use Illuminate\Container\Container;

/**
 * A class using the container underneath
 *
 * @property \Arrounded\Arrounded                                                      arrounded
 * @property \Illuminate\Auth\AuthManager|\Illuminate\Auth\Guard                       auth
 * @property \Illuminate\Cache\Repository                                              cache
 * @property \Illuminate\Config\Repository                                             config
 * @property \Illuminate\Console\Application                                           artisan
 * @property \Illuminate\Cookie\CookieJar                                              cookie
 * @property \Illuminate\Database\DatabaseManager|\Illuminate\Database\Connection      db
 * @property \Illuminate\Encryption\Encrypter                                          encrypter
 * @property \Illuminate\Events\Dispatcher                                             events
 * @property \Illuminate\Filesystem\Filesystem                                         files
 * @property \Illuminate\Foundation\Application                                        app
 * @property \Illuminate\Hashing\HasherInterface                                       hash
 * @property \Illuminate\Html\FormBuilder                                              form
 * @property \Illuminate\Html\HtmlBuilder                                              html
 * @property \Illuminate\Http\Request                                                  request
 * @property \Illuminate\Log\Writer                                                    log
 * @property \Illuminate\Mail\Mailer                                                   mailer
 * @property \Illuminate\Pagination\Factory                                            paginator
 * @property \Illuminate\Queue\QueueManager                                            queue
 * @property \Illuminate\Redis\Database                                                redis
 * @property \Illuminate\Remote\RemoteManager                                          remote
 * @property \Illuminate\Routing\Redirector                                            redirect
 * @property \Illuminate\Routing\Router                                                router
 * @property \Illuminate\Routing\UrlGenerator                                          url
 * @property \Illuminate\Session\SessionManager                                        session
 * @property \Illuminate\Translation\Translator                                        translator
 * @property \Illuminate\Validation\Factory                                            validator
 * @property \Illuminate\View\Compilers\BladeCompiler                                  blade.compiler
 * @property \Illuminate\View\Factory                                                  view
 */
trait UsesContainer
{
	/**
	 * The IoC Container
	 *
	 * @type Container
	 */
	protected $app;

	/**
	 * Default construct for a container-based class
	 *
	 * @param Container $app
	 */
	public function __construct(Container $app)
	{
		$this->app = $app;
	}

	/**
	 * Get an entry from the Container
	 *
	 * @param string $key
	 *
	 * @return object
	 */
	public function __get($key)
	{
		return $this->app[$key];
	}
}
