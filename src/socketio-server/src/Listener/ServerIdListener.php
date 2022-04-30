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
namespace Hyperf\SocketIOServer\Listener;

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeMainServerStart;
use Hyperf\Server\Event\MainCoroutineServerStart;
use Hyperf\SocketIOServer\SocketIO;
use Swoole\Atomic;

class ServerIdListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            BeforeMainServerStart::class,
            MainCoroutineServerStart::class,
        ];
    }

    public function process(object $event): void
    {
        SocketIO::$serverId = uniqid();
        SocketIO::$messageId = new Atomic();
    }
}
