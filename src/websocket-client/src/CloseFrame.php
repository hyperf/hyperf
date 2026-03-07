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

namespace Hyperf\WebSocketClient;

use Swoole\WebSocket\CloseFrame as SwCloseFrame;

class CloseFrame extends Frame
{
    public int $code = WEBSOCKET_CLOSE_NORMAL;

    public string $reason = '';

    public function __construct(SwCloseFrame $frame)
    {
        parent::__construct($frame);

        $this->code = $frame->code;
        $this->reason = $frame->reason;
    }
}
