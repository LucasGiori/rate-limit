<?php

namespace RateLimit;

use Memcached;
use RateLimit\Exception\CannotUseRateLimiter;
use RateLimit\Exception\LimitExceeded;

class MemcachedRateLimiter extends DefaultRateLimiter implements RateLimiterInterface
{
    private const MEMCACHED_SECONDS_LIMIT = 2592000; // 30 days in seconds

    public function __construct(Rate $rate, private Memcached $memcached, private string $keyPrefix = '')
    {
        /** @see https://www.php.net/manual/en/memcached.increment.php#111187 */
        if ($memcached->getOption(option: Memcached::OPT_BINARY_PROTOCOL) !== 1) {
            throw new CannotUseRateLimiter(message:  'Memcached "OPT_BINARY_PROTOCOL" option should be set to "true".');
        }

        parent::__construct(rate: $rate);
    }

    public function limit(string $identifier): void
    {
        $limitKey = $this->limitKey(identifier: $identifier);

        $current = $this->getCurrent(limitKey: $limitKey);
        if ($current >= $this->rate->getOperations()) {
            throw LimitExceeded::for(identifier: $identifier, rate: $this->rate);
        }

        $this->updateCounter(limitKey: $limitKey);
    }

    public function limitSilently(string $identifier): Status
    {
        $limitKey = $this->limitKey(identifier: $identifier);
        $timeKey = $this->timeKey(identifier: $identifier);

        $current = $this->getCurrent(limitKey: $limitKey);
        if ($current <= $this->rate->getOperations()) {
            $current = $this->updateCounterAndTime(limitKey: $limitKey, timeKey: $timeKey);
        }

        return Status::from(
            identifier: $identifier,
            current: $current,
            limit: $this->rate->getOperations(),
            resetTime: time() + max(0, $this->rate->getInterval() - $this->getElapsedTime($timeKey))
        );
    }

    private function limitKey(string $identifier): string
    {
        return sprintf('%s%s:%d', $this->keyPrefix, $identifier, $this->rate->getInterval());
    }

    private function timeKey(string $identifier): string
    {
        return sprintf('%s%s:%d:time', $this->keyPrefix, $identifier, $this->rate->getInterval());
    }

    private function getCurrent(string $limitKey): int
    {
        return (int) $this->memcached->get(key: $limitKey);
    }

    private function updateCounterAndTime(string $limitKey, string $timeKey): int
    {
        $current = $this->updateCounter(limitKey: $limitKey);

        if ($current === 1) {
            $this->memcached->add(
                key: $timeKey,
                value: time(),
                expiration: $this->intervalToMemcachedTime(interval: $this->rate->getInterval())
            );
        }

        return $current;
    }

    private function updateCounter(string $limitKey): int
    {
        $current = $this->memcached->increment(
            key: $limitKey,
            offset: 1,
            initial_value: 1,
            expiry: $this->intervalToMemcachedTime(interval: $this->rate->getInterval())
        );

        return $current === false ? 1 : $current;
    }

    private function getElapsedTime(string $timeKey): int
    {
        return time() - (int) $this->memcached->get($timeKey);
    }

    /**
     * Interval to Memcached expiration time.
     *
     * @see https://www.php.net/manual/en/memcached.expiration.php
     */
    private function intervalToMemcachedTime(int $interval): int
    {
        return $interval <= self::MEMCACHED_SECONDS_LIMIT ? $interval : time() + $interval;
    }
}
