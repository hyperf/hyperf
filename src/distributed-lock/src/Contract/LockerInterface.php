<?php
/**
 * LockInterface.php
 *
 * Author: wangyi <chunhei2008@qq.com>
 *
 * Date:   2019/9/7 18:19
 * Copyright: (C) 2014, Guangzhou YIDEJIA Network Technology Co., Ltd.
 */

namespace Hyperf\DistributedLock\Contract;


use Hyperf\DistributedLock\Mutex;

interface LockerInterface
{
    /**
     * @param string $resource
     * @param int    $ttl
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