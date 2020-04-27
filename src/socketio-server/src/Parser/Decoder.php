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

class Decoder
{
    public function decode($payload): Packet
    {
        // type
        $i = 0;
        $type = $payload[$i];
        $nsp = '/';
        ++$i;

        //TODO: Support attachment

        // namespace
        if ($payload[$i] === '/') {
            ++$i;
            while ($payload[$i] !== ',') {
                $nsp .= $payload[$i];
                ++$i;
            }
            ++$i;
        }

        // id
        $id = '';
        while (mb_strlen($payload) > $i && filter_var($payload[$i], FILTER_VALIDATE_INT) !== false) {
            $id .= $payload[$i];
            ++$i;
        }

        // data
        $data = json_decode(mb_substr($payload, $i), true) ?? [];
        return Packet::create([
            'type' => $type,
            'nsp' => $nsp,
            'id' => $id,
            'data' => $data,
        ]);
    }
}
