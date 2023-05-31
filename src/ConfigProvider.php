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

use Nasustop\HapiRateLimit\Listener\BeforeMainServerStartListener;
use Nasustop\HapiRateLimit\Listener\BeforeWorkerStartListener;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
            ],
            'listeners' => [
                BeforeMainServerStartListener::class,
                BeforeWorkerStartListener::class,
            ],
            'commands' => [
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
        ];
    }
}
