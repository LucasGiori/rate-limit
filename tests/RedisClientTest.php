<?php

namespace RateLimit\Tests;

use PHPUnit\Framework\TestCase;
use Predis\ClientInterface;
use RateLimit\RedisClient;
use Redis;

class RedisClientTest extends TestCase
{
    public function testMustValidateTheReturnOfAllFunctions()
    {
        $clientRedisOriginalMock = $this->createMock(originalClassName: Redis::class);

        $clientRedisOriginalMock->expects($this->once())->method(constraint: 'get')->willReturn(value: 1);
        $clientRedisOriginalMock->expects($this->once())->method(constraint: 'incr')->willReturn(value: 2);
        $clientRedisOriginalMock->expects($this->once())->method(constraint: 'expire')->willReturn(value: true);
        $clientRedisOriginalMock->expects($this->once())->method(constraint: 'pttl')->willReturn(value: 1000000);
        $clientRedisOriginalMock->expects($this->once())->method(constraint: 'ttl')->willReturn(value: 1);
        $clientRedisOriginalMock->expects($this->once())->method(constraint: 'del')->willReturn(value: 1);

        $redisClient = new RedisClient(redis: $clientRedisOriginalMock);

        static::assertEquals(expected: 1, actual: $redisClient->get("identifier"));
        static::assertEquals(expected: 2, actual: $redisClient->incr("identifier"));
        static::assertEquals(expected: true, actual: $redisClient->expire("identifier", 100));
        static::assertEquals(expected: 1000000, actual: $redisClient->pttl("identifier"));
        static::assertEquals(expected: 1, actual: $redisClient->ttl("identifier"));
        static::assertEquals(expected: 1, actual: $redisClient->del("identifier"));
    }
}