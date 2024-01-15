<?php

namespace App\Exceptions\AmoCrmApi;

use Throwable;

class NotFoundActionException extends \Exception
{
    protected $message = 'Not found Action';

    public function __construct($message = null, $code = 0, Throwable $previous = null) {
        if (empty($message)) {
            $message = $this->message;
        }
        parent::__construct($message, $code, $previous);
    }
}
