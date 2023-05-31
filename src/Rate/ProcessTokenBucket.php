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

use Swoole\Lock;

use const SWOOLE_MUTEX;

class ProcessTokenBucket implements TokenBucketInterface
{
    protected static ?Lock $lock = null;

    protected static int $last_capacity = 0;

    protected static int $refill_time = 0;

    /**
     * @param int $capacity 令牌桶容量
     * @param int $rate 令牌桶生成令牌速率
     * @param int $interval 令牌桶生成令牌时间间隔
     */
    public function __construct(
        protected int $capacity = 1000,
        protected int $rate = 1000,
        protected int $interval = 1
    ) {
        if (is_null(self::$lock)) {
            self::$lock = new Lock(SWOOLE_MUTEX);
        }
    }

    public function getToken(int $tokens): bool
    {
        self::$lock->lock();
        $this->refillTokens();
        if ($tokens > self::$last_capacity) {
            self::$lock->unlock();
            return false;
        }
        $last_capacity = self::$last_capacity = self::$last_capacity - $tokens;
        if (self::$last_capacity < 0) {
            self::$last_capacity = 0;
        }
        self::$lock->unlock();
        return $last_capacity >= 0;
    }

    public function refillTokens(): bool
    {
        $now = time();
        $nowRefillTime = $now - $now % $this->interval;
        if (self::$refill_time >= $nowRefillTime) {
            return false;
        }
        $newTokens = intval(($nowRefillTime - self::$refill_time) / $this->interval * $this->rate);
        $tokens = min($this->capacity, $newTokens + self::$last_capacity);
        self::$last_capacity = $tokens;
        self::$refill_time = $nowRefillTime;
        return true;
    }
}
