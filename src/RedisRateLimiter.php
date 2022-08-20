<?php

namespace RateLimit;

use RateLimit\Exception\LimitExceeded;

class RedisRateLimiter extends DefaultRateLimiter implements RateLimiterInterface
{
    public function __construct(Rate $rate, private RedisClientInterface $redis, private string $keyPrefix = '')
    {
        parent::__construct(rate: $rate);
    }

    public function limit(string $identifier): void
    {
        $key = $this->makeKey(identifier: $identifier);

        $current = $this->getCurrent(key: $key);

        if ($current >= $this->rate->getOperations()) {
            throw LimitExceeded::for(identifier: $identifier, rate: $this->rate);
        }

        $this->updateCounter(key: $key);
    }

    public function limitSilently(string $identifier): Status
    {
        $key = $this->makekey(identifier: $identifier);

        $current = $this->getCurrent(key: $key);

        if ($current <= $this->rate->getOperations()) {
            $current = $this->updateCounter(key: $key);
        }

        return Status::from(
            identifier: $identifier,
            current: $current,
            limit: $this->rate->getOperations(),
            resetTime: time() + $this->ttl(key: $key)
        );
    }

    private function makeKey(string $identifier): string
    {
        return sprintf(
            '%s%s:%s',
            $this->keyPrefix,
            $identifier,
            $this->rate->getInterval()
        );
    }

    private function getCurrent(string $key): int
    {
        return $this->redis->get($key);
    }

    private function updateCounter(string $key): int
    {
        $current = $this->redis->incr(key: $key);

        if ($current == 1) {
            $this->redis->expire($key, $this->rate->getInterval());
        }

        return $current;
    }

    private function ttl(string $key): int
    {
        return max((int) $this->redis->ttl($key), 0);
    }
}
