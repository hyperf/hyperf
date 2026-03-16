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

class JobMessage implements MessageInterface
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

        return [
            $this->job,  // Compatible with old version, will be removed at v3.2
            $this->attempts,  // Compatible with old version, will be removed at v3.2
            'job' => $this->job,
            'attempts' => $this->attempts,
        ];
    }

    public function __unserialize(array $data): void
    {
        if (array_is_list($data)) { // Compatible with old version, will be removed at v3.2
            $data = [
                'job' => $data[0],
                'attempts' => $data[1],
            ];
        }

        $job = $data['job'];

        if ($job instanceof UnCompressInterface) {
            $job = $job->uncompress();
        }

        $this->job = $job;
        $this->attempts = $data['attempts'];
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
}
