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
namespace Hyperf\Retry;

use Hyperf\Support\Backoff;

class BackoffStrategy implements SleepStrategyInterface
{
    private Backoff $backoff;

    public function __construct(int $base)
    {
        $this->backoff = new Backoff($base);
    }

    public function sleep(): void
    {
        $this->backoff->sleep();
    }
}
