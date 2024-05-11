<?php

namespace RateLimit\Tests;

use PHPUnit\Framework\TestCase;
use RateLimit\Exception\LimitExceeded;
use RateLimit\Rate;
use RateLimit\RedisClient;
use RateLimit\RedisClientInterface;
use RateLimit\RedisRateLimiter;
use RateLimit\Status;
use Redis;
use RedisException;

class RedisRateLimiterTest extends TestCase
{
    private const KEY_PREFIX = 'test_';

    private RedisClientInterface $redis;

    public function setUp(): void
    {
        $redis = new Redis(['connectTimeout' => 1, 'readTimeout' => 1]);


        try {
            $redis->ping();
        } catch (RedisException) {
            $this->markTestSkipped(
                message: 'O Ambiente de desenvolvimento com o redis precisa estar up para executar o teste!'
            );
        }
        $this->redis = new RedisClient(redis: $redis->connect(host: 'redis', port: 6379, timeout: 1, read_timeout: 1));
    }

    public function testShouldRunTheLimiterWithNoExceptions(): void
    {
        $identifier = 'no-exceptions-method';

        $rate = Rate::perDay(operations: 10000);

        $redisRateLimiter = new RedisRateLimiter(rate: $rate, redis: $this->redis, keyPrefix: self::KEY_PREFIX);

        $this->assertNull($redisRateLimiter->limit(identifier: $identifier));

        $this->clearKeyUsedByTest(key: $this->makeKeyTest(identifier: $identifier, rate: $rate));
    }

    public function testShouldRunSilentLimiterAndReturnAStatusInstance(): void
    {
        $identifier = 'silent';

        $rate = Rate::perDay(operations: 30000);

        $redisRateLimiter = new RedisRateLimiter(rate: $rate, redis: $this->redis, keyPrefix: self::KEY_PREFIX);

        $this->assertInstanceOf(
            expected: Status::class,
            actual: $redisRateLimiter->limitSilently(identifier: $identifier)
        );

        $this->clearKeyUsedByTest(key: $this->makeKeyTest(identifier: $identifier, rate: $rate));
    }

    public function testShouldReturnALimitExceeded(): void
    {
        $identifier = 'exception';

        $rate = Rate::perDay(operations: 2);

        $key = $this->makeKeyTest(identifier: $identifier, rate: $rate);

        $this->redis->incr(key: $key);
        $this->redis->incr(key: $key);

        $redisRateLimiter = new RedisRateLimiter(rate: $rate, redis: $this->redis, keyPrefix: self::KEY_PREFIX);

        try {
            $redisRateLimiter->limit(identifier: $identifier);
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
        $this->redis->del($key);
    }
}
