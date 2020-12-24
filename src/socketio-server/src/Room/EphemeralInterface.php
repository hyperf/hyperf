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

namespace Hyperf\SocketIOServer\Room;

interface EphemeralInterface
{
    /**
     * @return EphemeralInterface
     *
     * Set the ttl in milliseconds for a sid. Should be called during
     * starting up.
     */
    public function setTtl(int $ms): EphemeralInterface;

    /**
     * Renew the ttl for a sid. Should be called in a heartbeat handler.
     */
    public function renew(string $sid): void;

    /**
     * CleanUpExpired cleans up all expired sids in fixed interval.
     * It will not return until worker exited.
     * Should be called during starting up.
     */
    public function cleanupExpired(): void;
}
