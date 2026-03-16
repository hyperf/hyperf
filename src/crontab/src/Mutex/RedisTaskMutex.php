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

use Hyperf\Coordinator\Timer;
use Hyperf\Crontab\Crontab;
use Hyperf\Redis\RedisFactory;

class RedisTaskMutex implements TaskMutex
{
    private Timer $timer;

    public function __construct(private RedisFactory $redisFactory)
    {
        $this->timer = new Timer();
    }

    /**
     * Attempt to obtain a task mutex for the given crontab.
     */
    public function create(Crontab $crontab): bool
    {
        $redis = $this->redisFactory->get($crontab->getMutexPool());
        $mutexName = $this->getMutexName($crontab);
        $attempted = (bool) $redis->set($mutexName, $crontab->getName(), ['NX', 'EX' => $crontab->getMutexExpires()]);
        $attempted && $this->timer->tick(1, function () use ($mutexName, $redis) {
            if ($redis->expire($mutexName, $redis->ttl($mutexName) + 1) === false) {
                return Timer::STOP;
            }
        });
        return $attempted;
    }

    /**
     * Determine if a task mutex exists for the given crontab.
     */
    public function exists(Crontab $crontab): bool
    {
        return (bool) $this->redisFactory->get($crontab->getMutexPool())->exists(
            $this->getMutexName($crontab)
        );
    }

    /**
     * Clear the task mutex for the given crontab.
     */
    public function remove(Crontab $crontab)
    {
        $this->redisFactory->get($crontab->getMutexPool())->del(
            $this->getMutexName($crontab)
        );
    }

    protected function getMutexName(Crontab $crontab)
    {
        return 'framework' . DIRECTORY_SEPARATOR . 'crontab-' . sha1($crontab->getName() . $crontab->getRule());
    }
}
