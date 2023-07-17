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

interface JobInterface
{
    /**
     * Handle the job.
     */
    public function handle();

    public function setMaxAttempts(int $maxAttempts): static;

    public function getMaxAttempts(): int;

    public function setPool(string $pool): static;

    public function getPool(): string;

    public function setDelay(int $delay): static;

    public function getDelay(): int;
}
