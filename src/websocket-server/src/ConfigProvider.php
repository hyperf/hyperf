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

namespace Hyperf\WebSocketServer;

use Hyperf\WebSocketServer\Exception\WebSocketHandShakeException;

// Will remove at v3.2
class_alias(WebSocketHandShakeException::class, 'Hyperf\WebSocketServer\Exception\WebSocketHandeShakeException');

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'listeners' => [
                Listener\InitSenderListener::class,
                Listener\OnPipeMessageListener::class,
            ],
        ];
    }
}
