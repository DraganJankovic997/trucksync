<?php

namespace App\Exceptions;

use Exception;

class RouteStopUsageNotAllowedException extends Exception
{
    public function __construct(string $message = 'Only the convoy leader assigned to this route can submit a route stop usage review.')
    {
        parent::__construct($message);
    }
}
