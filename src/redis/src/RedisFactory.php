<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Redis;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Redis\Exception\InvalidRedisProxyException;

class RedisFactory
{
    /**
     * @var RedisProxy[]
     */
    protected $proxies;

    public function __construct(ConfigInterface $config)
    {
        $redisConfig = $config->get('redis');

        foreach ($redisConfig as $poolName => $item) {
            $this->proxies[$poolName] = make(RedisProxy::class, ['pool' => $poolName]);
        }
    }

    /**
     * @return \Redis|RedisProxy
     */
    public function get(string $poolName)
    {
        $proxy = $this->proxies[$poolName] ?? null;
        if (! $proxy instanceof RedisProxy) {
            throw new InvalidRedisProxyException('Invalid Redis proxy.');
        }

        return $proxy;
    }
}
