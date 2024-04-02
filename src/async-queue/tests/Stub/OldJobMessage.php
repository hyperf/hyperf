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

namespace HyperfTest\AsyncQueue\Stub;

use Hyperf\AsyncQueue\JobMessage;
use Hyperf\Contract\UnCompressInterface;

class OldJobMessage extends JobMessage
{
    public function __unserialize(array $data): void
    {
        [$job, $attempts] = $data;

        if ($job instanceof UnCompressInterface) {
            $job = $job->uncompress();
        }

        $this->job = $job;
        $this->attempts = $attempts;
    }
}
