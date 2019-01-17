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
     * push a job to queue.
     * @param JobInterface $job
     */
    public function push(JobInterface $job);

    /**
     * push a delay job to queue.
     * @param JobInterface $job
     * @param int $delay
     */
    public function delay(JobInterface $job, int $delay = 0);

    /**
     * pop a job
     * @param int $timeout
     */
    public function pop(int $timeout = 0);

    /**
     * ack a job.
     * @param $data
     */
    public function ack($data);

    /**
     * push a job to failed queue.
     * @param $data
     */
    public function fail($data);

    /**
     * consume a queue.
     */
    public function consume();
}
