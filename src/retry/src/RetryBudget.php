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

use Hyperf\Coordinator\Timer;
use SplQueue;

class RetryBudget implements RetryBudgetInterface
{
    private SplQueue $budget;

    private ?int $timerId = null;

    private float $maxToken;

    private Timer $timer;

    /**
     * @param int $ttl Seconds
     */
    public function __construct(private int $ttl, private int $minRetriesPerSec, private float $percentCanRetry)
    {
        $this->maxToken = ($this->minRetriesPerSec / $this->percentCanRetry) * $this->ttl;
        $this->budget = new SplQueue();
        $this->timer = new Timer();
    }

    public function __destruct()
    {
        if (! isset($this->timerId)) {
            return;
        }
        $this->timer->clear($this->timerId);
    }

    public function init()
    {
        if (isset($this->timerId)) {
            return;
        }
        for ($i = 0; $i < $this->minRetriesPerSec / $this->percentCanRetry; ++$i) {
            $this->produce();
        }
        $this->timerId = $this->timer->tick(1, function () {
            for ($i = 0; $i < $this->minRetriesPerSec / $this->percentCanRetry; ++$i) {
                $this->produce();
            }
            while ($this->hasOverflown()) {
                $this->budget->dequeue();
            }
        });
    }

    public function consume(bool $dryRun = false): bool
    {
        $this->init();
        if ($this->budget->count() < 1 / $this->percentCanRetry) {
            return false;
        }
        if ($dryRun) {
            return true;
        }
        for ($i = 0; $i < 1 / $this->percentCanRetry; ++$i) {
            $this->budget->dequeue();
        }
        return true;
    }

    public function produce(): void
    {
        $t = microtime(true) + $this->ttl;
        $this->budget->push($t);
    }

    public function hasOverflown(): bool
    {
        return (! $this->budget->isEmpty() && $this->budget->bottom() <= microtime(true))
            || $this->budget->count() > $this->maxToken;
    }
}
