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

namespace Hyperf\Retry;

use SplQueue;
use Swoole\Timer;

class RetryBudget implements RetryBudgetInterface
{
    /**
     * Seconds.
     * @var int
     */
    private $ttl;

    /**
     * @var int
     */
    private $minRetriesPerSec;

    /**
     * @var float
     */
    private $percentCanRetry;

    /**
     * @var SplQueue
     */
    private $budget;

    /**
     * @var int
     */
    private $timerId;

    public function __construct(int $ttl, int $minRetriesPerSec, float $percentCanRetry)
    {
        $this->ttl = $ttl;
        $this->minRetriesPerSec = $minRetriesPerSec;
        $this->percentCanRetry = $percentCanRetry;
        $this->budget = new SplQueue();
        for ($i = 0; $i < $minRetriesPerSec; ++$i) {
            $this->produce();
        }
        $this->timerId = Timer::tick(1000, function () use ($minRetriesPerSec) {
            for ($i = 0; $i < $minRetriesPerSec / $this->percentCanRetry; ++$i) {
                $this->produce();
            }
            while (! $this->budget->isEmpty()
                && $this->budget->top() <= microtime(true)
            ) {
                $this->budget->dequeue();
            }
        });
    }

    public function __destruct()
    {
        Timer::clear($this->timerId);
    }

    public function consume(bool $dryRun = false): bool
    {
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
}
