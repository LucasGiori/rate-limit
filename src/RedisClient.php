<?php

namespace RateLimit;

use Predis\ClientInterface;
use Redis;

final class RedisClient implements RedisClientInterface
{
    public function __construct(
        private Redis|ClientInterface $redis
    ) {
    }

    public function get(string $key): int
    {
        return (int) $this->redis->get(key: $key);
    }

    public function incr(string $key): int
    {
        return $this->redis->incr(key: $key);
    }

    public function expire(string $key, int $ttl): bool
    {
        return $this->redis->expire($key, $ttl);
    }

    public function pttl(string $key): int|bool
    {
        return $this->redis->pttl(key: $key);
    }

    public function ttl(string $key): int|bool
    {
        return $this->redis->ttl(key: $key);
    }

    public function del(string $key): int
    {
        return $this->redis->del($key);
    }
}
