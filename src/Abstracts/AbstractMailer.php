<?php
namespace Arrounded\Abstracts;

use Illuminate\Mail\Mailer;
use Illuminate\Mail\Message;
use Illuminate\Support\Collection;
use Swift_RfcComplianceException;
use User;

/**
 * An abstract class for mail services
 */
abstract class AbstractMailer
{
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
	 * Apply modifiers to an email
	 *
	 * @param  Message $email
	 *
	 * @return Message
	 */
	abstract protected function alterMessage(Message $email);

	/**
	 * Send the invitations
	 *
	 * @return integer Number of mails sent
	 */
	public function send()
	{
		$view = '_emails.'.$this->template;

		foreach ($this->recipients as $recipient) {
			$data = $this->gatherData($recipient);
			$this->mailer->send($view, $data, function (Message $message) use ($recipient) {
				try {
					$message = $message->to($recipient->email);
				} catch (Swift_RfcComplianceException $exception) {
				}

				return $this->alterMessage($message);
			});
		}

		return sizeof($this->recipients);
	}

	/**
	 * Gather the data for the email
	 *
	 * @param User $recipient
	 *
	 * @return array
	 */
	protected function gatherData(User $recipient)
	{
		$data = new Collection($this->databag);
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
		$recipients = (array) $recipients;
		$users      = new Collection($recipients);
		$column     = 'email';

		if (empty($recipients)) {
			return $users;
		}

		// Convert recipients to User instances if necessary
		if (!$recipients[0] instanceof User) {
			$column = (int) $recipients[0] === 0 ? 'email' : 'id';
			$users  = User::whereIn($column, $recipients)->get();
		}

		// If no users were found, create instances on the run
		if ($users->isEmpty()) {
			$users = new Collection;
			foreach ($recipients as $recipient) {
				$users[] = new User([$column => $recipient]);
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
