<?php
namespace Arrounded\Abstracts;

use Arrounded\Abstracts\Models\AbstractModel;
use Illuminate\Auth\UserInterface;
use Illuminate\Mail\Mailer;
use Illuminate\Mail\Message;
use Illuminate\Queue\Jobs\Job;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Swift_RfcComplianceException;

/**
 * An abstract class for mail services
 */
abstract class AbstractMailer
{
	/**
	 * The mailer instance
	 *
	 * @type Mailer
	 */
	protected $mailer;

	/**
	 * The queue manager instance
	 *
	 * @type QueueManager
	 */
	protected $queue;

	/**
	 * Whether mails should be queued or sent
	 *
	 * @type bool
	 */
	protected $queued = true;

	/**
	 * The user sending invitations
	 *
	 * @type User
	 */
	protected $from;

	/**
	 * The friends to invite
	 *
	 * @type Collection
	 */
	protected $recipients = array();

	/**
	 * The message's subject
	 *
	 * @type string
	 */
	protected $subject;

	/**
	 * The template to use
	 *
	 * @type string
	 */
	protected $template;

	/**
	 * The core databag all messages inherit
	 *
	 * @type array
	 */
	protected $databag = array();

	/**
	 * Build a new FriendsInviter
	 *
	 * @param Mailer $mailer
	 */
	public function __construct(Mailer $mailer, QueueManager $queue)
	{
		$this->mailer = $mailer;
		$this->queue  = $queue;
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// SETUP /////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Set the user inviting the others
	 *
	 * @param UserInterface $user
	 */
	public function setSender(UserInterface $user)
	{
		$this->databag = array(
			'from' => $user,
		);
	}

	/**
	 * @param string $subject
	 */
	public function setSubject($subject)
	{
		$this->subject = $subject;
	}

	/**
	 * Set the friend(s) to invite
	 *
	 * @param string|array $recipients
	 *
	 * @return Collection
	 */
	public function setRecipients($recipients)
	{
		$this->recipients = $this->translateRecipients($recipients);

		return $this->recipients;
	}

	/**
	 * @return Collection
	 */
	public function getRecipients()
	{
		return $this->recipients;
	}

	/**
	 * Set the template to use
	 *
	 * @param string $template
	 */
	public function setTemplate($template)
	{
		$this->template = $template;
	}

	/**
	 * @param boolean $queue
	 */
	public function setQueue($queue)
	{
		$this->queued = $queue;
	}

	/**
	 * Change the core databag
	 *
	 * @param array $databag
	 */
	public function setDatabag(array $databag)
	{
		$this->databag = array_merge($this->databag, $databag);
	}

	/**
	 * @return array
	 */
	public function getDatabag()
	{
		return $this->databag;
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// SENDING ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get modifiers to apply to an email
	 *
	 * @return array
	 */
	public function getParameters()
	{
		$parameters = [];
		if ($this->subject) {
			$parameters['subject'] = $this->subject;
		}

		return $parameters;
	}

	/**
	 * Send the invitations
	 *
	 * @return integer Number of mails sent
	 */
	public function send()
	{
		$view       = '_emails.'.$this->template;
		$parameters = $this->getParameters();

		foreach ($this->recipients as $recipient) {
			$data      = $this->gatherData($recipient);
			$recipient = $recipient->email;

			// Send to queue or immediately
			if ($this->queued) {
				$this->queue->push(function (Job $job) use ($view, $data, $recipient, $parameters) {
					$this->sendMessage($view, $data, $recipient, $parameters);
					$job->delete();
				});
			} else {
				$this->sendMessage($view, $data, $recipient, $parameters);
			}
		}

		return count($this->recipients);
	}

	/**
	 * Send a message to someone
	 *
	 * @param string $view       The view to render
	 * @param array  $data       The data to pass to the view
	 * @param string $recipient  The recipient's email
	 * @param array  $parameters Additional settings on the Message
	 */
	protected function sendMessage($view, $data, $recipient, $parameters)
	{
		// Set locale if possible
		if ($locale = array_get($data, 'locale')) {
			$this->setLocale($locale);
		}

		$this->mailer->send($view, $data, function (Message $message) use ($recipient, $parameters) {
			// Catch errors
			try {
				$message = $message->to($recipient);
			} catch (Swift_RfcComplianceException $exception) {
				// Email is invalid, skip it
			}

			// Set additional parameters
			foreach ($parameters as $key => $value) {
				$message->$key($value);
			}

			return $message;
		});
	}

	/**
	 * Gather the data for the email
	 *
	 * @param AbstractModel $recipient
	 *
	 * @return array
	 */
	protected function gatherData(AbstractModel $recipient)
	{
		$data         = new Collection($this->databag);
		$data['user'] = $recipient;

		// Pass locale to view just in case
		if ($locale = $recipient->locale) {
			$data['locale'] = $locale;
		}

		return $data->toArray();
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Translate recipients from keys or emails to a collection of users
	 *
	 * @param array|string $recipients
	 *
	 * @return Collection
	 */
	protected function translateRecipients($recipients)
	{
		if (!is_array($recipients) || !array_key_exists(0, $recipients)) {
			$recipients = [$recipients];
		}

		$users  = new Collection($recipients);
		$column = 'email';
		$model  = Config::get('auth.model');

		if (empty($recipients)) {
			return $users;
		}

		// Convert recipients to User instances if necessary
		if (!$recipients[0] instanceof $model) {
			$column = (int) $recipients[0] === 0 ? 'email' : 'id';
			$users  = $model::whereIn($column, $recipients)->get();
		}

		// If no users were found, create instances on the run
		if ($users->isEmpty()) {
			$users = new Collection();
			foreach ($recipients as $recipient) {
				$users[] = new $model([$column => $recipient]);
			}
		}

		// Filter invalid addresses
		foreach ($users as $key => $user) {
			if (!filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
				unset($users[$key]);
			}
		}

		return $users;
	}

	/**
	 * Set the application's language
	 *
	 * @param string $locale
	 */
	protected function setLocale($locale)
	{
		$app = app();
		if ($app->bound('polyglot.translator')) {
			$app['polyglot.translator']->setInternalLocale($locale);
		} else {
			$app->setLocale($locale);
		}
	}
}
