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

namespace Hyperf\DistributedLock\Driver;

use Hyperf\DistributedLock\Contract\LockerInterface;
use Psr\Container\ContainerInterface;

interface DriverInterface extends LockerInterface
{
    public function __construct(ContainerInterface $container, array $config);
}
