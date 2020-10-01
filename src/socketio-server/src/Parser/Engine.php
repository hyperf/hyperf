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
namespace Hyperf\SocketIOServer\Parser;

class Engine
{
    const OPEN = '0';

    const CLOSE = '1';

    const PING = '2';

    const PONG = '3';

    const MESSAGE = '4';

    const UPGRADE = '5';

    const NOOP = '6';
}
