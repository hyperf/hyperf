<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\AsyncQueue;

use Hyperf\Contract\CodeDegenerateInterface;
use Hyperf\Contract\CodeGenerateInterface;
use Serializable;

class Message implements MessageInterface, Serializable
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
        if ($this->job->getMaxAttempts() > $this->attempts++) {
            return true;
        }
        return false;
    }

    public function serialize()
    {
        if ($this->job instanceof CodeGenerateInterface) {
            $this->job = $this->job->generate();
        }

        return serialize([$this->job, $this->attempts]);
    }

    public function unserialize($serialized)
    {
        [$job, $attempts] = unserialize($serialized);
        if ($job instanceof CodeDegenerateInterface) {
            $job = $job->degenerate();
        }

        $this->job = $job;
        $this->attempts = $attempts;
    }
}
