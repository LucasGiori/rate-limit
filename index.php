<?php

require_once __DIR__ . '/vendor/autoload.php';

use RateLimit\Exception\LimitExceeded;
use RateLimit\MemcachedRateLimiter;
use RateLimit\Rate;
use RateLimit\RedisClient;
use RateLimit\RedisRateLimiter;

$rate = Rate::perMinute(operations: 4);

$key = $_GET['key'] ?? "default";

/** ------------------------------------------------------------------------------- */

$redis = (new Redis())->connect('redis', 6379);

$pRedis = new Predis\Client([
    'scheme' => 'tcp',
    'host' => 'redis',
    'port' => 6379
]);

/*
 * Pode escolher qual adapter vai usar para integrar Predis ou Redis
 */
$redis = new RedisClient($pRedis);

/** ------------------------------------------------------------------------------- */
//$rateLimiter = new RedisRateLimiter(rate: $rate, redis: $redis);
//
//$status = $rateLimiter->limitSilently(identifier: $key);
//$resetAtInSeconds = $status->getResetAtInSeconds();

/** ------------------------------------------------------------------------------- */

$memcached = new Memcached();
$memcached->addServer(host: 'memcached', port: 11211);
$memcached->setOption(option: Memcached::OPT_BINARY_PROTOCOL, value: true);

if (empty($memcached->getStats())) {
    http_response_code(500);
    header("Content-Type: application/json");
    echo "Internal Server Error 500 - memcached offline";
    return;
}
$memcachedRateLimiter = new MemcachedRateLimiter(rate: $rate, memcached: $memcached);

$status = $memcachedRateLimiter->limitSilently(identifier: $key);
$resetAtInSeconds = $status->getResetAtInSeconds();
/** ------------------------------------------------------------------------------- */

$response = json_encode([
    "success" => $status->isSuccess(),
    "identifier" => $status->getIdentifier(),
    "remainingAttempts" => $status->getRemainingAttempts(),
    "limit" => $status->getLimit(),
    "resetAtInSeconds" => $resetAtInSeconds,
    "now" => (new DateTime())->format('Y-m-d h:m:s')
]);

if ($status->isSuccess()) {
    http_response_code(200);
    header("Content-Type: application/json");
    echo "Ok 200 ${response}";
} else {
    http_response_code(429);
    header("Content-Type: application/json");
    header("Retry-After: ${resetAtInSeconds}");
    echo "Fail 429 ${response}";
}

//try {
//    $rateLimiter->limit(identifier: $key);
//    http_response_code(200);
//    echo "Ok 200 key: ${key}";
//} catch (LimitExceeded $limitExceeded) {
//    http_response_code(429);
//    $qtdOperations = $limitExceeded->getRate()->getOperations();
//    $teste = $limitExceeded->getRate()->getInterval();
//    echo "Fail 429 key: ${key} Operations: ${qtdOperations} Interval: ${teste}";
//}

