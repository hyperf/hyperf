<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Crontab\Mutex;

use Hyperf\Crontab\Crontab;
use Hyperf\Di\Annotation\Inject;

class RedisTaskMutex implements TaskMutex
{
    /**
     * @Inject()
     * @var \Hyperf\Redis\RedisFactory
     */
    private $redisFactory;

    /**
     * Attempt to obtain a task mutex for the given crontab.
     *
     * @param \Hyperf\Crontab\Crontab $crontab
     * @return bool
     */
    public function create(Crontab $crontab): bool
    {
        return (bool) $this->redisFactory->get($crontab->getMutexPool())->set(
            $this->getMutexName($crontab), $crontab->getName(), ['NX', 'EX' => $crontab->getMutexExpires()]
        );
    }

    /**
     * Determine if a task mutex exists for the given crontab.
     *
     * @param \Hyperf\Crontab\Crontab $crontab
     * @return bool
     */
    public function exists(Crontab $crontab): bool
    {
        return (bool) $this->redisFactory->get($crontab->getMutexPool())->exists(
            $this->getMutexName($crontab)
        );
    }

    /**
     * Clear the task mutex for the given crontab.
     *
     * @param \Hyperf\Crontab\Crontab $crontab
     */
    public function remove(Crontab $crontab)
    {
        $this->redisFactory->get($crontab->getMutexPool())->del(
            $this->getMutexName($crontab)
        );
    }

    protected function getMutexName(Crontab $crontab)
    {
        return 'framework'.DIRECTORY_SEPARATOR.'crontab-'.sha1($crontab->getName().$crontab->getRule());
    }
}
