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
namespace HyperfTest\SocketIOServer\Stub;

use Hyperf\SocketIOServer\Room\EphemeralInterface;
use Hyperf\SocketIOServer\Room\MemoryAdapter;

class EphemeralAdapter extends MemoryAdapter implements EphemeralInterface
{
    public static $isRenew = false;

    public function setTtl(int $ms): EphemeralInterface
    {
        return $this;
    }

    public function renew(string $sid): void
    {
        static::$isRenew = true;
    }

    public function cleanupExpired(): void
    {
    }
}
