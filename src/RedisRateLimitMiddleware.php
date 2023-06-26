<?php

declare(strict_types=1);
/**
 * This file is part of Hapi.
 *
 * @link     https://www.nasus.top
 * @document https://wiki.nasus.top
 * @contact  xupengfei@xupengfei.net
 * @license  https://github.com/nasustop/hapi-rate-limit/blob/master/LICENSE
 */
namespace Nasustop\HapiRateLimit;

use Hyperf\Context\ApplicationContext;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Redis\RedisFactory;
use Nasustop\HapiRateLimit\Rate\RedisTokenBucket;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RedisRateLimitMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): PsrResponseInterface
    {
        /* @var $redisFactory RedisFactory */
        $redisFactory = make(RedisFactory::class);
        $redisRate = new RedisTokenBucket(
            redis: $redisFactory->get('default'),
            key: 'hapi_rate_limit',
            capacity: 1000,
            rate: 1000,
            interval: 1
        );
        if (! $redisRate->getToken(1)) {
            /* @var $response ResponseInterface */
            $response = make(ResponseInterface::class);
            return $response->json([
                'code' => 429,
                'msg' => 'To Many Requests.',
            ]);
        }
        return $handler->handle($request);
    }
}
