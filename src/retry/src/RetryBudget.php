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

    /**
     * @var float|int
     */
    private $maxToken;

    public function __construct(int $ttl, int $minRetriesPerSec, float $percentCanRetry)
    {
        $this->ttl = $ttl;
        $this->minRetriesPerSec = $minRetriesPerSec;
        $this->percentCanRetry = $percentCanRetry;
        $this->maxToken = ($this->minRetriesPerSec / $this->percentCanRetry) * $this->ttl;
        $this->budget = new SplQueue();
    }

    public function __destruct()
    {
        if (! isset($this->timerId)) {
            return;
        }
        Timer::clear($this->timerId);
    }

    public function init()
    {
        if (isset($this->timerId)) {
            return;
        }
        for ($i = 0; $i < $this->minRetriesPerSec / $this->percentCanRetry; ++$i) {
            $this->produce();
        }
        $this->timerId = Timer::tick(1000, function () {
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
