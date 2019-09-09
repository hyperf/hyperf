<?php
/**
 * LockInterface.php
 *
 * Author: wangyi <chunhei2008@qq.com>
 *
 * Date:   2019/9/7 18:19
 * Copyright: (C) 2014, Guangzhou YIDEJIA Network Technology Co., Ltd.
 */

namespace Hyperf\DistributedLocks\Contract;


interface LockerInterface
{
    /**
     * @param $resource
     * @param $ttl
     * @return mixed
     *
     * Author: wangyi <chunhei2008@qq.com>
     */
    public function lock($resource, $ttl);


    /**
     * @param array $lock
     * @return mixed
     *
     * Author: wangyi <chunhei2008@qq.com>
     */
    public function unlock(array $lock);

}