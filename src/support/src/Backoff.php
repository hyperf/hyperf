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

namespace Hyperf\Support;

use InvalidArgumentException;

class Backoff
{
    /**
     * Max backoff.
     */
    private const CAP = 60 * 1000; // 1 minute

    /**
     * Backoff interval.
     */
    private int $currentMs;

    /**
     * @param int the first backoff in milliseconds
     */
    public function __construct(private int $firstMs = 0)
    {
        if ($firstMs < 0) {
            throw new InvalidArgumentException(
                'first backoff interval must be greater or equal than 0'
            );
        }

        if ($firstMs > Backoff::CAP) {
            throw new InvalidArgumentException(
                sprintf(
                    'first backoff interval must be less or equal than %d milliseconds',
                    self::CAP
                )
            );
        }

        $this->currentMs = $firstMs;
    }

    /**
     * Sleep until the next execution.
     */
    public function sleep(): void
    {
        if ($this->currentMs === 0) {
            return;
        }

        usleep($this->currentMs * 1000);

        // update backoff using Decorrelated Jitter
        // see: https://aws.amazon.com/blogs/architecture/exponential-backoff-and-jitter/
        $this->currentMs = rand($this->firstMs, $this->currentMs * 3);

        if ($this->currentMs > self::CAP) {
            $this->currentMs = self::CAP;
        }
    }

    /**
     * Get the next backoff for logging, etc.
     * @return int next backoff
     */
    public function nextBackoff(): int
    {
        return $this->currentMs;
    }
}
