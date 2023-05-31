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
use Hyperf\Framework\Event\BeforeMainServerStart;
use Swoole\Table;

class BeforeMainServerStartListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            BeforeMainServerStart::class,
        ];
    }

    public function process(object $event): void
    {
        if ($event instanceof BeforeMainServerStart) {
            $table = new Table(1024);
            $table->column('tokens', Table::TYPE_INT);
            $table->column('refill_time', Table::TYPE_INT);
            $table->create();
            $table->set('rate_limit', [
                'tokens' => 0,
                'refill_time' => 0,
            ]);
            $event->server->rate_limit_table = $table;
        }
    }
}
