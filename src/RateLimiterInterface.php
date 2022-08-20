<?php

namespace RateLimit;

interface RateLimiterInterface
{
    public function limit(string $identifier): void;

    public function limitSilently(string $identifier): Status;
}
