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

namespace Hyperf\SocketIOServer\SidProvider;

interface SidProviderInterface
{
    public function getSid(int $fd): string;

    public function isLocal(string $sid): bool;

    public function getFd(string $sid): int;
}
