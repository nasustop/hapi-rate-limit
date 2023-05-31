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
namespace Nasustop\HapiRateLimit\Rate;

use Hyperf\Coroutine\Coroutine;
use Nasustop\HapiRateLimit\ProcessData;
use Swoole\Table;

class MemoryTokenBucket implements TokenBucketInterface
{
    protected Table $table;

    public function __construct(
        protected int $capacity = 1000,
        protected int $rate = 1000,
        protected int $interval = 1
    ) {
        if (empty($this->table)) {
            $this->table = ProcessData::$table;
        }
    }

    public function getToken(int $tokens): bool
    {
        $this->refillTokens();
        var_dump('echo', ProcessData::$work_id, Coroutine::id(), $this->table->get('rate_limit', 'tokens'));
        if ($tokens > $this->table->get('rate_limit', 'tokens')) {
            return false;
        }
        $last_capacity = $this->table->decr('rate_limit', 'tokens', $tokens);
        if ($this->table->get('rate_limit', 'tokens') < 0) {
            $this->table->set('rate_limit', [
                'tokens' => 0,
                'refill_time' => $this->table->get('rate_limit', 'refill_time'),
            ]);
        }
        return $last_capacity >= 0;
    }

    public function refillTokens(): bool
    {
        $now = time();
        $nowRefillTime = $now - $now % $this->interval;
        if ($this->table->get('rate_limit', 'refill_time') >= $nowRefillTime) {
            return false;
        }
        $newTokens = intval(($nowRefillTime - $this->table->get('rate_limit', 'refill_time')) / $this->interval * $this->rate);
        $tokens = min($this->capacity, $newTokens + $this->table->get('rate_limit', 'tokens'));
        $this->table->set('rate_limit', [
            'tokens' => $tokens,
            'refill_time' => $nowRefillTime,
        ]);
        return true;
    }
}
