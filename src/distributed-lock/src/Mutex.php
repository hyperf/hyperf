<?php
/**
 * Mutex.php
 *
 * Author: wangyi <chunhei2008@qq.com>
 *
 * Date:   2019-09-11 12:46
 * Copyright: (C) 2014, Guangzhou YIDEJIA Network Technology Co., Ltd.
 */

namespace Hyperf\DistributedLock;


class Mutex
{
    /**
     * @var array
     */
    private $context = [];

    /**
     * @var bool
     */
    private $isAcquired = false;

    /**
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * @param array $context
     * @return Mutex
     *
     * Author: wangyi <chunhei2008@qq.com>
     */
    public function setContext(array $context): self
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAcquired(): bool
    {
        return $this->isAcquired;
    }

    /**
     * @param bool $isAcquired
     * @return Mutex
     *
     * Author: wangyi <chunhei2008@qq.com>
     */
    public function setIsAcquired(bool $isAcquired = true): self
    {
        $this->isAcquired = $isAcquired;

        return $this;
    }
}