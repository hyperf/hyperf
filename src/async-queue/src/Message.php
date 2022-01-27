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

/**
 * @deprecated in v3.1, please use JobMessage instead
 */
class Message implements MessageInterface, Serializable
{
    protected int $attempts = 0;

    public function __construct(protected JobInterface $job)
    {
    }

    public function __serialize(): array
    {
        if ($this->job instanceof CompressInterface) {
            /* @phpstan-ignore-next-line */
            $this->job = $this->job->compress();
        }

        return [$this->job, $this->attempts];
    }

    public function __unserialize(array $data): void
    {
        [$job, $attempts] = $data;
        if ($job instanceof UnCompressInterface) {
            $job = $job->uncompress();
        }

        $this->job = $job;
        $this->attempts = $attempts;
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
            /* @phpstan-ignore-next-line */
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
