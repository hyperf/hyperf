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
    /**
     * @var \Hyperf\Nsq\Packer
     */
    protected $packer;

    public function __construct(Packer $packer)
    {
        $this->packer = $packer;
    }

    public function buildPub($topic, $message): string
    {
        $cmd = "PUB {$topic} \n";
        $size = $this->packer->packUInt32(strlen($message));

        return $cmd . $size . $message;
    }

    public function buildSub($topic, $channel): string
    {
        return "SUB {$topic} {$channel} \n";
    }

    public function buildRdy(int $count): string
    {
        return "RDY {$count} \n";
    }

    public function buildTouch($id): string
    {
        return "TOUCH {$id}\n";
    }

    public function buildFin($id): string
    {
        return "FIN {$id}\n";
    }

    public function buildReq($id, $timeout = 1): string
    {
        return "REQ {$id} {$timeout}\n";
    }
}
