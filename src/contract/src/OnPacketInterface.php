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

use Swoole\WebSocket\Server;

interface OnPacketInterface
{
    /**
     * @param Server $server
     * @param mixed $data
     * @param array $clientInfo
     */
    public function onPacket($server, $data, $clientInfo): void;
}
