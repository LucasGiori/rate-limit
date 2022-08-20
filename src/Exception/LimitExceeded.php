<?php

namespace RateLimit\Exception;

use RateLimit\Rate;
use RuntimeException;

final class LimitExceeded extends RuntimeException implements RateLimitException
{
    private string $identifier;

    private Rate $rate;

    public static function for(string $identifier, Rate $rate): self
    {
        $message = sprintf('Limit has been exceeded for indentifier: "%s"', $identifier);

        $exception = new self(message: $message);
        $exception->identifier = $identifier;
        $exception->rate = $rate;

        return $exception;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getRate(): Rate
    {
        return $this->rate;
    }
}
