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

class NoOpRetryBudget implements RetryBudgetInterface
{
    public function produce(): void
    {
    }

    public function consume(bool $dryRun = false): bool
    {
        return true;
    }
}
