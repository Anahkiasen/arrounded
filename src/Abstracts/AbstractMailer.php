<?php
namespace Arrounded\Abstracts;

use Auth;
use Config;
use Illuminate\Mail\Mailer;
use Illuminate\Mail\Message;
use Illuminate\Support\Collection;
use Swift_RfcComplianceException;

/**
 * An abstract class for mail services
 */
abstract class AbstractMailer
{
	/**
	 * Whether mails should be queued or sent
	 *
	 * @type bool
	 */
	protected $queue = true;

	/**
	 * The mailer instance
	 *
	 * @var Mailer
	 */
	protected $mailer;

	/**
	 * The user sending invitations
	 *
	 * @var User
	 */
	protected $from;

	/**
	 * The friends to invite
	 *
	 * @var array
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
	 * @var string
	 */
	protected $template;

	/**
	 * The core databag all messages inherit
	 *
	 * @var array
	 */
	protected $databag = array();

	/**
	 * Build a new FriendsInviter
	 *
	 * @param Mailer $mailer
	 */
	public function __construct(Mailer $mailer)
	{
		$this->mailer = $mailer;
		$this->from   = Auth::user();
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////////// SETUP /////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Set the user inviting the others
	 *
	 * @param User $user
	 */
	public function setSender(User $user)
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
		$this->queue = $queue;
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
		$method     = $this->queue ? 'queue' : 'send';
		$parameters = $this->getParameters();

		foreach ($this->recipients as $recipient) {
			$data = $this->gatherData($recipient);
			$this->mailer->$method($view, $data, function (Message $message) use ($recipient, $parameters) {

				// Catch errors
				try {
					$message = $message->to($recipient->email);
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

		return sizeof($this->recipients);
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
}
