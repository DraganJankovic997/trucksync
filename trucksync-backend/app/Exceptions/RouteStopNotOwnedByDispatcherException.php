<?php

namespace App\Exceptions;

use RuntimeException;

class RouteStopNotOwnedByDispatcherException extends RuntimeException
{
    public function __construct(string $message = 'You cannot update route stop services for a route you did not create.')
    {
        parent::__construct($message);
    }
}
