<?php
namespace Arrounded\Traits;

use Arrounded\Collection;
use Mail;

/**
 * A trait for a mailable person.
 */
trait Mailable
{
    /**
     * Send an email to a model.
     *
     * @param string $subject
     * @param string $view
     * @param array  $data
     * @param bool   $condition
     */
    public function emailOn($subject, $view, $data = [], $condition = true)
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
