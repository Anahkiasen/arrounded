<?php
namespace Arrounded\Traits;

use Illuminate\Container\Container;

/**
 * A class using the container underneath
 *
 * @property \Illuminate\Foundation\Application                                        app
 * @property \Illuminate\Console\Application                                           artisan
 * @property \Illuminate\Auth\AuthManager|\Illuminate\Auth\Guard                       auth
 * @property \Illuminate\View\Compilers\BladeCompiler                                  blade.compiler
 * @property \Illuminate\Cache\Repository                                              cache
 * @property \Illuminate\Config\Repository                                             config
 * @property \Illuminate\Cookie\CookieJar                                              cookie
 * @property \Illuminate\Encryption\Encrypter                                          encrypter
 * @property \Illuminate\Database\DatabaseManager|\Illuminate\Database\Connection      db
 * @property \Illuminate\Events\Dispatcher                                             events
 * @property \Illuminate\Filesystem\Filesystem                                         files
 * @property \Illuminate\Html\FormBuilder                                              form
 * @property \Illuminate\Hashing\HasherInterface                                       hash
 * @property \Illuminate\Html\HtmlBuilder                                              html
 * @property \Illuminate\Http\Request                                                  request
 * @property \Illuminate\Translation\Translator                                        translator
 * @property \Illuminate\Log\Writer                                                    log
 * @property \Illuminate\Mail\Mailer                                                   mailer
 * @property \Illuminate\Pagination\Factory                                            paginator
 * @property \Illuminate\Queue\QueueManager                                            queue
 * @property \Illuminate\Routing\Redirector                                            redirect
 * @property \Illuminate\Redis\Database                                                redis
 * @property \Illuminate\Routing\Router                                                router
 * @property \Illuminate\Session\SessionManager                                        session
 * @property \Illuminate\Remote\RemoteManager                                          remote
 * @property \Illuminate\Routing\UrlGenerator                                          url
 * @property \Illuminate\Validation\Factory                                            validator
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
