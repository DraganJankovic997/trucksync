<?php

namespace App\Exceptions;

use RuntimeException;

class InvalidCredentialsException extends RuntimeException
{
    public function __construct(string $message = 'The provided credentials are invalid.')
    {
        parent::__construct($message);
    }
}
