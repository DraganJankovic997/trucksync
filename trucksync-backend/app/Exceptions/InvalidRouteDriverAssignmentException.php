<?php

namespace App\Exceptions;

use RuntimeException;

class InvalidRouteDriverAssignmentException extends RuntimeException
{
    public function __construct(string $message = 'Selected drivers must belong to your dispatcher profile.')
    {
        parent::__construct($message);
    }
}
