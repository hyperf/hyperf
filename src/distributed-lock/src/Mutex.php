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
