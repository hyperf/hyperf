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

namespace Hyperf\AsyncQueue;

use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\Conditionable\Conditionable;
use Hyperf\Context\ApplicationContext;

class PendingDispatch
{
    use Conditionable;

    protected int $delay = 0;

    protected string $pool = 'default';

    public function __construct(protected JobInterface $job)
    {
    }

    public function __destruct()
    {
        ApplicationContext::getContainer()
            ->get(DriverFactory::class)
            ->get($this->pool)
            ->push($this->job, $this->delay);
    }

    public function setMaxAttempts(int $maxAttempts): static
    {
        $this->job->setMaxAttempts($maxAttempts);
        return $this;
    }

    public function onPool(string $pool): static
    {
        $this->pool = $pool;
        return $this;
    }

    public function delay(int $delay): static
    {
        $this->delay = $delay;
        return $this;
    }
}
