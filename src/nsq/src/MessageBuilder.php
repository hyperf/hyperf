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

namespace Hyperf\Nsq;

class MessageBuilder
{

    public function buildPub(string $topic, string $message): string
    {
        $cmd = "PUB {$topic}\n";
        $size = Packer::packUInt32(strlen($message));

        return $cmd . $size . $message;
    }

    public function buildSub(string $topic, string $channel): string
    {
        return "SUB {$topic} {$channel}\n";
    }

    public function buildRdy(int $count): string
    {
        return "RDY {$count}\n";
    }

    public function buildTouch(string $id): string
    {
        return "TOUCH {$id}\n";
    }

    public function buildFin(string $id): string
    {
        return "FIN {$id}\n";
    }

    public function buildReq(string $id, int $timeout = 1): string
    {
        return "REQ {$id} {$timeout}\n";
    }

    public function buildNop(): string
    {
        return "NOP\n";
    }
}
