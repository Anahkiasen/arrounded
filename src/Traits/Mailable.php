<?php
namespace Arrounded\Traits;

use Arrounded\Collection;
use Mail;

/**
 * A trait for a mailable person
 */
trait Mailable
{
	/**
	 * Send an email to a model
	 *
	 * @param string  $subject
	 * @param string  $view
	 * @param array   $data
	 * @param boolean $condition
	 *
	 * @return void
	 */
	public function emailOn($subject, $view, $data = array(), $condition = true)
	{
		$recipient = $this->email;
		if (!$condition || !$recipient) {
			return;
		}

		// Serialize data
		$data['user'] = $this;
		$data         = Collection::serialize($data);

		// Send email
		Mail::queue($view, $data, function ($message) use ($recipient, $subject) {
			$message->subject($subject)->to($recipient);
		});
	}
}
