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

class LocalSidProvider implements SidProviderInterface
{
    public function getSid(int $fd): string
    {
        return (string) $fd;
    }

    public function isLocal(string $sid): bool
    {
        return true;
    }

    public function getFd(string $sid): int
    {
        return (int) $sid;
    }
}
