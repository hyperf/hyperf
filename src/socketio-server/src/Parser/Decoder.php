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

class Decoder
{
    public function decode($payload): Packet
    {
        // type
        $i = 0;
        $type = $payload[$i];
        $nsp = '/';
        $query = [];
        ++$i;

        //TODO: Support attachment

        // namespace
        if (isset($payload[$i]) && $payload[$i] === '/') {
            ++$i;
            while ($payload[$i] !== ',' && $payload[$i] !== '?') {
                $nsp .= $payload[$i];
                ++$i;
            }
            if ($payload[$i] === '?') {
                ++$i;
                $query = '';
                while ($payload[$i] !== ',') {
                    $query .= $payload[$i];
                    ++$i;
                }
                $result = [];
                parse_str($query, $result);
                $query = $result;
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
            'query' => $query,
        ]);
    }
}
