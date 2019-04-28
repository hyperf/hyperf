<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\AsyncQueue;

class Message implements MessageInterface
{
    /**
     * @var JobInterface
     */
    protected $job;

    /**
     * @var int
     */
    protected $attempts = 0;

    public function __construct(JobInterface $job)
    {
        $this->job = $job;
    }

    public function job(): JobInterface
    {
        return $this->job;
    }

    public function attempts(): bool
    {
        if ($this->job->getMaxAttempts() > ++$this->attempts) {
            return true;
        }
        return false;
    }
}
