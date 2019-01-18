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

namespace Hyperf\Queue\Driver;

use Hyperf\Queue\JobInterface;

interface DriverInterface
{
    /**
     * Push a job to queue.
     * @param JobInterface $job
     */
    public function push(JobInterface $job);

    /**
     * Push a delay job to queue.
     * @param JobInterface $job
     * @param int $delay
     */
    public function delay(JobInterface $job, int $delay = 0);

    /**
     * Pop a job from queue.
     * @param int $timeout
     */
    public function pop(int $timeout = 0);

    /**
     * Ack a job.
     * @param $data
     */
    public function ack($data);

    /**
     * Push a job to failed queue.
     * @param $data
     */
    public function fail($data);

    /**
     * Consume jobs from a queue.
     */
    public function consume();
}
