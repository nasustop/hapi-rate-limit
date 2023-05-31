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
namespace Nasustop\HapiRateLimit\Listener;

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeWorkerStart;
use Nasustop\HapiRateLimit\ProcessData;
use Swoole\Table;

class BeforeWorkerStartListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            BeforeWorkerStart::class,
        ];
    }

    public function process(object $event): void
    {
        if ($event instanceof BeforeWorkerStart) {
            ProcessData::$work_id = $event->workerId;
            $table = $event->server->rate_limit_table ?? null;
            if ($table instanceof Table) {
                ProcessData::$table = $table;
            }
        }
    }
}
