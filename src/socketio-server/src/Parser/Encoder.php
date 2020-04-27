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

namespace Hyperf\SocketIOServer\Parser;

class Encoder
{
    public function encode(Packet $packet): string
    {
        $noData = false;
        if (! is_array($packet->data)) {
            $noData = true;
        }
        return implode('', [
            $packet->type,
            $packet->nsp === '/' ? '' : $packet->nsp . ',',
            $packet->id,
            $noData ? '' : json_encode($packet->data),
        ]);
    }
}
