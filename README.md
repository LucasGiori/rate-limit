# Rate Limit
### Build
[![Testes e Cobertura de Código](https://github.com/LucasGiori/rate-limit/actions/workflows/test-and-coverage.yml/badge.svg)](https://github.com/LucasGiori/rate-limit/actions/workflows/test-and-coverage.yml)

### Descrição
O limitador de taxa pode ser usado para limitar a taxa na qual determinada operação pode ser executada. Atualmente tem disponível uma implementação para o <code>Redis</code> e <code>Memcached</code>.

### Uso

#### Limitador padrão com exception:
```php

use RateLimit\Exception\LimitExceeded;
use RateLimit\Rate;
use RateLimit\RedisRateLimiter;
use Redis;

$redis = (new Redis())->connect('redis', 6379);
// Ou
$predis = new Predis\Client(['scheme' => 'tcp', 'host' => 'redis', 'port' => 6379]);


$redisClient = new RedisClient(redis: $predis);
// Ou
$redisClient = new RedisClient(redis: $redis);


$redisRateLimiter = new RedisRateLimiter(rate: Rate::perMinute(100), redis: $redisClient);

$apiKey = 'user:123456';

try {
    $redisRateLimiter->limit(identifier: $apiKey);
    //on success
} catch (LimitExceeded $exception) {
   //on limit exceeded
}
```

#### Limitador silencioso
```php

$redisRateLimiter = new RedisRateLimiter(rate: Rate::perMinute(100), redis: $redsClient);

$apiKey = 'user:123456';

$status = $redisRateLimiter->limitSilently(identifier: $apiKey);

$status->getIdentifier(); // string
$status->isSuccess(); //  bool
$status->getLimit(); //  int
$status->getRemainingAttempts(); //  int
$status->getResetAt(); //  DateTimeImmutable
$status->getResetAtInSeconds(); //  int
```

#### Adaptadores disponíveis:
 - Redis
 - Memcached