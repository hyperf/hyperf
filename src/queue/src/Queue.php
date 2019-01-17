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

namespace Hyperf\Queue;

class Queue implements QueueInterface
{
    protected $job;

    /**
     * @var int
     */
    protected $attempts = 0;

    public function __construct(JobInterface $job, Config $config)
    {
        $this->job = $job;
        $this->config = $config;
    }

    public function handle()
    {
        try {
            $this->job->handle();
        } catch (\Throwable $ex) {
        }
    }
}
