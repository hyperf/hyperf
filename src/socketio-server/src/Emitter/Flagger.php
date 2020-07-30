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
namespace Hyperf\SocketIOServer\Emitter;

trait Flagger
{
    /**
     * @return int | bool flags
     */
    protected function guessFlags(bool $compress)
    {
        // older swoole version
        if (! defined('SWOOLE_WEBSOCKET_FLAG_FIN')) {
            return true;
        }

        if ($compress) {
            return SWOOLE_WEBSOCKET_FLAG_FIN | SWOOLE_WEBSOCKET_FLAG_COMPRESS;
        }

        return SWOOLE_WEBSOCKET_FLAG_FIN;
    }
}
