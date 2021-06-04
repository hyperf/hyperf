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
    public const OPEN = '0';

    public const CLOSE = '1';

    public const PING = '2';

    public const PONG = '3';

    public const MESSAGE = '4';

    public const UPGRADE = '5';

    public const NOOP = '6';
}
