<?php

namespace RateLimit;

abstract class DefaultRateLimiter
{
    public function __construct(
        protected Rate $rate
    ) {
    }
}
