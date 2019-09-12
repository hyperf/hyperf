<?php

declare(strict_types=1);

/**
 * Created by PhpStorm.
 * Date: 2019/9/10
 * Time: 17:46
 * Email: languageusa@163.com
 * Author: Dickens7
 */

namespace Hyperf\Session\Handler;

use Hyperf\Redis\RedisProxy;
use Hyperf\Redis\RedisFactory;
use Hyperf\Utils\ApplicationContext;

/**
 * Class RedisSessionHandler
 * @package Hyperf\Session\Handler
 */
class RedisHandler implements HandlerInterface
{
    public $poolName = 'default';

    public function setPoolName($poolName)
    {
        $this->poolName = $poolName;
    }

    /**
     * @return \Redis
     */
    public function getRedis()
    {
        return $redis = ApplicationContext::getContainer()->get(RedisFactory::class)->get($this->poolName);
    }

    public function open(string $sessionPath, string $sessionName): bool
    {
        return true;
    }

    public function read(string $sessionId): array
    {
        return $this->decode($this->getRedis()->get($sessionId));
    }

    public function write(string $sessionId, array $sessionData, $maxLifetime): bool
    {
        return (bool)$this->getRedis()->setex($sessionId, $maxLifetime, $this->encode($sessionData));
    }

    public function destroy(string $sessionId): bool
    {
        return (bool)$this->getRedis()->delete($sessionId);
    }

    public function encode($data)
    {
        return serialize($data);
    }

    public function decode($data): array
    {
        if (!$data) {
            return [];
        }
        return unserialize($data);
    }
}