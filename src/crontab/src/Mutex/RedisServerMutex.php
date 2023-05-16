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
namespace Hyperf\Crontab\Mutex;

use Hyperf\Collection\Arr;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Crontab\Crontab;
use Hyperf\Redis\RedisFactory;

class RedisServerMutex implements ServerMutex
{
    private null|string $macAddress;

    public function __construct(private RedisFactory $redisFactory)
    {
        $this->macAddress = $this->getMacAddress();
    }

    /**
     * Attempt to obtain a server mutex for the given crontab.
     */
    public function attempt(Crontab $crontab): bool
    {
        if ($this->macAddress === null) {
            return false;
        }

        $redis = $this->redisFactory->get($crontab->getMutexPool());
        $mutexName = $this->getMutexName($crontab);

        $result = $redis->set($mutexName, $this->macAddress, ['NX', 'EX' => $crontab->getMutexExpires()]);

        if ($result) {
            Coroutine::create(function () use ($crontab, $redis, $mutexName) {
                $exited = CoordinatorManager::until(Constants::WORKER_EXIT)->yield($crontab->getMutexExpires());
                $exited && $redis->del($mutexName);
            });
            return true;
        }

        return $redis->get($mutexName) === $this->macAddress;
    }

    /**
     * Get the server mutex for the given crontab.
     */
    public function get(Crontab $crontab): string
    {
        return (string) $this->redisFactory->get($crontab->getMutexPool())->get(
            $this->getMutexName($crontab)
        );
    }

    protected function getMutexName(Crontab $crontab)
    {
        return 'hyperf' . DIRECTORY_SEPARATOR . 'crontab-' . sha1($crontab->getName() . $crontab->getRule()) . '-sv';
    }

    protected function getMacAddress(): ?string
    {
        $macAddresses = swoole_get_local_mac();

        foreach (Arr::wrap($macAddresses) as $name => $address) {
            if ($address && $address !== '00:00:00:00:00:00') {
                return $name . ':' . str_replace(':', '', $address);
            }
        }

        return null;
    }
}
