# HapiRateLimit
hyperf的限流组件

## 安装
```
composer require nasustop/hapi-rate-limit
```

## 使用方法
```
// 进程级限流，每个进程独立的限流服务，受进程重启影响
use Nasustop\HapiRateLimit\Rate\ProcessTokenBucket;
// 服务级限流，多进程共享限流服务，受服务重启影响【推荐使用】
use Nasustop\HapiRateLimit\Rate\MemoryTokenBucket;
// 应用级限流，多服务共享限流服务，不受服务重启影响，但受限于redis服务的性能【推荐使用】
use Nasustop\HapiRateLimit\Rate\RedisTokenBucket;


$processRate = new ProcessTokenBucket(1000, 1000, 1);
if (! $processRate->getToken(1)) {
    $response = ApplicationContext::getContainer()->get(ResponseInterface::class);
    return $response->json([
        'code' => 429,
        'msg' => 'To Many Requests.',
    ]);
}
$memoryRate = new MemoryTokenBucket(1000, 1000, 1);
if (! $memoryRate->getToken(1)) {
    $response = ApplicationContext::getContainer()->get(ResponseInterface::class);
    return $response->json([
        'code' => 429,
        'msg' => 'To Many Requests.',
    ]);
}
$redisRate = new RedisTokenBucket(
    redis: ApplicationContext::getContainer()->get(RedisFactory::class)->get('default'),
    key: 'hapi_rate_limit',
    capacity: 1000,
    rate: 1000,
    interval: 1
);
if (! $redisRate->getToken(1)) {
    $response = ApplicationContext::getContainer()->get(ResponseInterface::class);
    return $response->json([
        'code' => 429,
        'msg' => 'To Many Requests.',
    ]);
}
```
