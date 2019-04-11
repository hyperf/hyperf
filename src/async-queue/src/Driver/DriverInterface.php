<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\AsyncQueue\Driver;

use Hyperf\AsyncQueue\JobInterface;

interface DriverInterface
{
    /**
     * Push a job to queue.
     */
    public function push(JobInterface $job): bool;

    /**
     * Push a delay job to queue.
     */
    public function delay(JobInterface $job, int $delay = 0): bool;

    /**
     * Pop a job from queue.
     */
    public function pop(int $timeout = 0): array;

    /**
     * Ack a job.
     *
     * @param $data
     */
    public function ack($data): bool;

    /**
     * Push a job to failed queue.
     *
     * @param $data
     */
    public function fail($data): bool;

    /**
     * Consume jobs from a queue.
     */
    public function consume(): void;

    /**
     * Reload failed message into waiting queue.
     */
    public function reload(): int;

    /**
     * Delete all failed message from failed queue.
     */
    public function flush(): bool;

    /**
     * Return info for current queue.
     */
    public function info(): array;
}
