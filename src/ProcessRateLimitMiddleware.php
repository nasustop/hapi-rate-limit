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
use Nasustop\HapiRateLimit\Rate\ProcessTokenBucket;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ProcessRateLimitMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): PsrResponseInterface
    {
        $processRate = new ProcessTokenBucket(1000, 1000, 1);
        if (! $processRate->getToken(1)) {
            $response = ApplicationContext::getContainer()->get(ResponseInterface::class);
            return $response->json([
                'code' => 429,
                'msg' => 'To Many Requests.',
            ]);
        }
        return $handler->handle($request);
    }
}
