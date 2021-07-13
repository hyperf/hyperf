<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\DistributedLock\Contract;

use Hyperf\DistributedLock\Mutex;

interface LockerInterface
{
    /**
     * @param string $resource
     * @param int $ttl
     * @return Mutex
     *
     * Author: wangyi <chunhei2008@qq.com>
     */
    public function lock(string $resource, int $ttl): Mutex;

    /**
     * @param Mutex $mutex
     *
     * Author: wangyi <chunhei2008@qq.com>
     */
    public function unlock(Mutex $mutex): void;
}
