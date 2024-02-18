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
namespace Hyperf\Support\Backoff;

use Hyperf\Collection\Arr;
use InvalidArgumentException;

class CustomBackoff
{
    private int $lastMilliseconds;

    /**
     * @param int $milliseconds backoff interval
     */
    public function __construct(private array $milliseconds)
    {
        if (empty($milliseconds)) {
            throw new InvalidArgumentException(
                'The backoff interval milliseconds cannot be empty.'
            );
        }

        $this->lastMilliseconds = array_pop($this->milliseconds);
    }

    /**
     * Sleep until the next execution.
     */
    public function sleep(): void
    {
        $ms = array_shift($this->milliseconds) ?? $this->lastMilliseconds;

        if ($ms === 0) {
            return;
        }

        usleep($ms * 1000);
    }

    /**
     * Get the next backoff for logging, etc.
     * @return int next backoff
     */
    public function nextBackoff(): int
    {
        return Arr::first($this->milliseconds);
    }
}
