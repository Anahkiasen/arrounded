<?php
namespace Arrounded\Validation;

use Exception;
use Illuminate\Support\MessageBag;

class ValidationException extends Exception
{
    /**
     * The bound errors.
     *
     * @type MessageBag
     */
    protected $errors;

    /**
     * Throw a new ValidationException.
     *
     * @param string     $message
     * @param MessageBag $errors
     */
    public function __construct($message, MessageBag $errors)
    {
        parent::__construct($message);

        $this->errors = $errors;
    }

    /**
     * Get the bound errors.
     *
     * @return MessageBag
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
