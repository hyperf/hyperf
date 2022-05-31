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

use Hyperf\Crontab\Crontab;
use Hyperf\Redis\RedisFactory;
use Hyperf\Utils\Coroutine;

class RedisTaskMutex implements TaskMutex
{
    use RenewalMutex;

    /**
     * @var RedisFactory
     */
    private $redisFactory;

    public function __construct(RedisFactory $redisFactory)
    {
        $this->redisFactory = $redisFactory;
    }

    /**
     * Attempt to obtain a task mutex for the given crontab.
     */
    public function create(Crontab $crontab): bool
    {
        $redis = $this->redisFactory->get($crontab->getMutexPool());
        $result = (bool) $redis->set(
            $this->getMutexName($crontab),
            $crontab->getName(),
            ['NX', 'EX' => $crontab->getMutexExpires()]
        );
        if ($result === false) {
            return $result;
        }
        // The coroutine renews the mutex
        Coroutine::create($this->renewalClosure($crontab, $redis));
        return $result;
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
