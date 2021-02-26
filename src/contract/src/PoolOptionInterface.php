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
namespace Hyperf\Contract;

interface PoolOptionInterface
{
    public function getMaxConnections(): int;

    public function getMinConnections(): int;

    public function getConnectTimeout(): float;

    public function getWaitTimeout(): float;

    public function getHeartbeat(): float;

    public function getMaxIdleTime(): float;
}
