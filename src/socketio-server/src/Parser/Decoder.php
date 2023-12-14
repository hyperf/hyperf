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

        // TODO: Support attachment

        // namespace
        if (isset($payload[$i]) && $payload[$i] === '/') {
            ++$i;
            while ($payload[$i] !== ',' && $payload[$i] !== '?') {
                $nsp .= $payload[$i];
                ++$i;
            }
            if ($payload[$i] === '?') {

                // Check if the SocketIO query data format is correct
                if (str_contains(substr($payload, $i + 1, -1), ',') === true) {
                    $queryStr = '';
                    while ($payload[$i] !== ',') {
                        $queryStr .= $payload[$i];
                        ++$i;
                    }
                    $result = [];
                    parse_str($queryStr, $result);
                    $query = $result;
                }
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
            'query' => $query,
        ]);
    }
}
