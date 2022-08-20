<?php

namespace RateLimit;

interface RedisClientInterface
{
    public function get(string $key): int;

    public function incr(string $key): int;

    public function expire(string $key, int $ttl): bool;

    public function pttl(string $key): int|bool;

    public function ttl(string $key): int|bool;

    public function del(string $key): int;
}
