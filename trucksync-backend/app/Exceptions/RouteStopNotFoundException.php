<?php

namespace App\Exceptions;

use RuntimeException;

class RouteStopNotFoundException extends RuntimeException
{
    public function __construct(string $message = 'Route stop not found.')
    {
        parent::__construct($message);
    }
}
