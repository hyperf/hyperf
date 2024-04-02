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

namespace Hyperf\Amqp\Message;

enum Type: string
{
    case DIRECT = 'direct';
    case FANOUT = 'fanout';
    case TOPIC = 'topic';

    public static function all(): array
    {
        return [
            self::DIRECT,
            self::FANOUT,
            self::TOPIC,
        ];
    }
}
