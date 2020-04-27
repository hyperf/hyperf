<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\SocketIOServer\Listener;

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeMainServerStart;
use Hyperf\SocketIOServer\SocketIO;
use Swoole\Atomic;

class ServerIdListener implements ListenerInterface
{
    public function listen(): array
    {
        return [BeforeMainServerStart::class];
    }

    /**
     * {@inheritdoc}
     */
    public function process(object $event)
    {
        SocketIO::$serverId = uniqid();
        SocketIO::$messageId = new Atomic();
    }
}
