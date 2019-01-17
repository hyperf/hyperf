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
    public function push(JobInterface $job);

    public function delay(JobInterface $job, int $delay = 0);

    public function pop(int $timeout = 0);

    public function ack($key);

    public function consume();
}
