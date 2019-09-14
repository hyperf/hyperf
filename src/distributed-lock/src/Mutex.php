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
    private $acquired = false;

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
    public function acquired(): bool
    {
        return $this->acquired;
    }

    /**
     * @param bool $acquired
     * @return Mutex
     *
     * Author: wangyi <chunhei2008@qq.com>
     */
    public function setAcquired(bool $acquired = true): self
    {
        $this->acquired = $acquired;

        return $this;
    }
}
