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

use Hyperf\Redis\RedisProxy;

class RedisTokenBucket
{
    /**
     * @param RedisProxy $redis redis连接
     * @param string $key 缓存key
     * @param int $capacity 令牌桶容量
     * @param int $rate 令牌桶生成令牌速率
     * @param int $interval 令牌桶生成令牌时间间隔
     */
    public function __construct(
        protected RedisProxy $redis,
        protected string $key = 'hapi_rate_limit',
        protected int $capacity = 1000,
        protected int $rate = 1000,
        protected int $interval = 1
    ) {
    }

    public function getToken(int $tokens): bool
    {
        $this->refillTokens();
        $result = $this->redis->eval("
            local tokens = tonumber(ARGV[1])
            local capacity = tonumber(redis.call('get', KEYS[1]) or 0);
            if tokens <= capacity then
                local last_num = tonumber(redis.call('DECRBY', KEYS[1], tokens))
                if last_num < 0 then
                    redis.call('SET', KEYS[1], 0)
                end
                return last_num
            end 
            return -1
        ", [$this->key, $tokens], 1);
        return is_int($result) && $result >= 0;
    }

    public function refillTokens(): bool
    {
        $result = $this->redis->eval("
            local capacity = tonumber(ARGV[1])
            local rate = tonumber(ARGV[2])
            local interval = tonumber(ARGV[3])
            
            local lastRefillTime = tonumber(redis.call('get', KEYS[1]..':lastRefillTime') or 0)
            
            local now = tonumber(redis.call('time')[1])
            local nowRefillTime = now - now % interval
            
            if lastRefillTime < nowRefillTime then
                local newTokens = math.floor((nowRefillTime - lastRefillTime) / interval * rate)
                local tokens = tonumber(redis.call('get', KEYS[1]) or 0)
                tokens = math.min(capacity, tokens + newTokens)
                redis.call('set', KEYS[1], tokens)
                redis.call('set', KEYS[1]..':lastRefillTime', nowRefillTime)
            end
            return 1
        ", [$this->key, $this->capacity, $this->rate, $this->interval], 1);
        if ($result === false) {
            throw new \RuntimeException(sprintf('refill token error: %s', $this->redis->getLastError()));
        }
        return is_int($result);
    }
}
