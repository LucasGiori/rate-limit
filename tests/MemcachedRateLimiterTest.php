<?php

namespace RateLimit\Tests;

use Memcached;
use PHPUnit\Framework\TestCase;
use RateLimit\Exception\LimitExceeded;
use RateLimit\MemcachedRateLimiter;
use RateLimit\Rate;
use RateLimit\Status;

class MemcachedRateLimiterTest extends TestCase
{
    private const KEY_PREFIX = 'test_';

    private const MEMCACHED_SECONDS_LIMIT = 2592000;

    private Memcached $memcached;

    public function setUp(): void
    {
        $memcached = new Memcached();
        $memcached->addServer(host: 'memcached', port: 11211);
        $memcached->setOption(option: Memcached::OPT_BINARY_PROTOCOL, value: true);

        if (empty($memcached->getStats())) {
            $this->markTestSkipped(
                message: 'O Ambiente de desenvolvimento com o memcached precisa estar up para executar o teste!'
            );
        } else {
            $this->memcached = $memcached;
        }
    }

    public function testShouldRunTheLimiterWithNoExceptions(): void
    {
        $identifier = 'no-exceptions-method';

        $rate = Rate::perDay(operations: 10000);

        $memcachedRateLimiter = new MemcachedRateLimiter(
            rate: $rate,
            memcached: $this->memcached,
            keyPrefix: self::KEY_PREFIX
        );

        $this->assertNull($memcachedRateLimiter->limit(identifier: $identifier));
        $this->clearKeyUsedByTest(key: $this->makeKeyTest(identifier: $identifier, rate: $rate));
    }

    public function testShouldRunSilentLimiterAndReturnAStatusInstance(): void
    {
        $identifier = 'silent';

        $rate = Rate::perDay(operations: 30000);

        $memcachedRateLimiter = new MemcachedRateLimiter(
            rate: $rate,
            memcached: $this->memcached,
            keyPrefix: self::KEY_PREFIX
        );

        $this->assertInstanceOf(
            expected: Status::class,
            actual: $memcachedRateLimiter->limitSilently(identifier: $identifier)
        );

        $this->clearKeyUsedByTest(key: $this->makeKeyTest(identifier: $identifier, rate: $rate));
    }

    public function testShouldReturnALimitExceeded(): void
    {
        $identifier = 'exception';

        $rate = Rate::perDay(operations: 2);

        $key = $this->makeKeyTest(identifier: $identifier, rate: $rate);

        $interval = $rate->getInterval();
        $this->memcached->increment(
            key: $key,
            offset: 1,
            initial_value: 1,
            expiry: $interval <= self::MEMCACHED_SECONDS_LIMIT ? $interval : time() + $interval
        );

        $this->memcached->increment(
            key: $key,
            offset: 1,
            initial_value: 1,
            expiry: $interval <= self::MEMCACHED_SECONDS_LIMIT ? $interval : time() + $interval
        );

        $memcachedRateLimiter = new MemcachedRateLimiter(
            rate: $rate,
            memcached: $this->memcached,
            keyPrefix: self::KEY_PREFIX
        );

        try {
            $memcachedRateLimiter->limit(identifier: $identifier);
            $this->fail(message: 'Deveria ter acontecido uma exceção de limite de taxa!');
        } catch (LimitExceeded $limitExceeded) {
            $this->assertEquals(expected: $identifier, actual: $limitExceeded->getIdentifier());
            $this->assertEquals(expected: 2, actual: $limitExceeded->getRate()->getOperations());

            $this->clearKeyUsedByTest(key: $key);
        }
    }

    private function makeKeyTest(string $identifier, Rate $rate)
    {
        return sprintf(
            '%s%s:%s',
            self::KEY_PREFIX,
            $identifier,
            $rate->getInterval()
        );
    }

    private function clearKeyUsedByTest(string $key): void
    {
        $this->memcached->delete($key);
    }
}
