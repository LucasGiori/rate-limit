<?php

namespace RateLimit\Exception;

use RuntimeException;

final class CannotUseRateLimiter extends RuntimeException implements RateLimitException
{
}
