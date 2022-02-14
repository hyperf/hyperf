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

use Hyperf\Contract\CompressInterface;
use Hyperf\Contract\UnCompressInterface;
use Serializable;

class Message implements MessageInterface, Serializable
{
    /**
     * @var CompressInterface|JobInterface|UnCompressInterface
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

    public function getAttempts(): int
    {
        return $this->attempts;
    }

    public function serialize()
    {
        if ($this->job instanceof CompressInterface) {
            $this->job = $this->job->compress();
        }

        return serialize([$this->job, $this->attempts]);
    }

    public function unserialize($serialized)
    {
        [$job, $attempts] = unserialize($serialized);
        if ($job instanceof UnCompressInterface) {
            $job = $job->uncompress();
        }

        $this->job = $job;
        $this->attempts = $attempts;
    }
}
