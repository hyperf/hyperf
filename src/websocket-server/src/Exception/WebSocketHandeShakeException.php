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

namespace Hyperf\WebSocketServer\Exception;

if (! class_exists('Hyperf\WebSocketServer\Exception\WebSocketHandeShakeException', false)) {
    /**
     * @deprecated since v3.1, will remove at v3.2. Please use Hyperf\WebSocketServer\Exception\WebSocketHandShakeException instead.
     */
    class WebSocketHandeShakeException extends WebSocketHandShakeException
    {
    }
}
