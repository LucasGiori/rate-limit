<?php

namespace RateLimit\Exception;

use Exception;

class InvalidValue extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
