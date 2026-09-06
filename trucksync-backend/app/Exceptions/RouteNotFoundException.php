<?php

namespace App\Exceptions;

use RuntimeException;

class RouteNotFoundException extends RuntimeException
{
    public function __construct(string $message = 'Route not found.')
    {
        parent::__construct($message);
    }
}
