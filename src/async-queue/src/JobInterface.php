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

use Throwable;

interface JobInterface
{
    public function fail(Throwable $e): void;

    /**
     * Handle the job.
     */
    public function handle();

    public function setMaxAttempts(int $maxAttempts): static;

    public function getMaxAttempts(): int;
}
