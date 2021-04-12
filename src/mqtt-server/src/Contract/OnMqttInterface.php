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
namespace Hyperf\MqttServer\Contract;

use Swoole\Coroutine\Server\Connection;
use Swoole\Server as SwooleServer;

interface OnMqttInterface
{
    /**
     * 1
     * @param Connection|SwooleServer $server
     */
    public function onMqConnect($server, int $fd, $fromId, $data);

    /**
     * 12
     * @param Connection|SwooleServer $server
     */
    public function onMqPingreq($server, int $fd, $fromId, $data): bool;

    /**
     * 14
     * @param Connection|SwooleServer $server
     */
    public function onMqDisconnect($server, int $fd, $fromId, $data): bool;

    /**
     * 3
     * @param Connection|SwooleServer $server
     */
    public function onMqPublish($server, int $fd, $fromId, $data);

    /**
     * 8
     * @param Connection|SwooleServer $server
     */
    public function onMqSubscribe($server, int $fd, $fromId, $data);

    /**
     * 10
     * @param Connection|SwooleServer $server
     */
    public function onMqUnsubscribe($server, int $fd, $fromId, $data);
}
