<?php

namespace App\Exceptions;

use RuntimeException;

class RouteNotOwnedByDispatcherException extends RuntimeException
{
    public function __construct(string $message = 'You cannot add route stops to a route you did not create.')
    {
        parent::__construct($message);
    }
}
