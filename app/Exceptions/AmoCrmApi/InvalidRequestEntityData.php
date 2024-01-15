<?php

namespace App\Exceptions\AmoCrmApi;

use PHPUnit\Event\Code\Throwable;

class InvalidRequestEntityData extends \Exception
{
    protected $message = 'Invalid request Entity data';

    public function __construct($message = null, $code = 0, Throwable $previous = null) {
    if (empty($message)) {
        $message = $this->message;
    }
    parent::__construct($message, $code, $previous);
}
}
