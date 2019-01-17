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

class RedisDriver extends Driver
{
    public function push(JobInterface $job)
    {
        // TODO: Implement push() method.
    }

    public function delay(JobInterface $job, int $delay = 0)
    {
        // TODO: Implement delay() method.
    }
}
