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

namespace Hyperf\Contract;

interface PoolOptionInterface
{
    public function getMaxConnections(): int;

    public function getMinConnections(): int;

    public function getConnectTimeout(): float;

    public function getWaitTimeout(): float;

    public function getHeartbeat(): float;
}
