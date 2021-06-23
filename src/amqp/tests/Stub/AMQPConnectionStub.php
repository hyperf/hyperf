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
namespace HyperfTest\Amqp\Stub;

use Hyperf\Amqp\AMQPConnection;

class AMQPConnectionStub extends AMQPConnection
{
    public function __construct()
    {
    }

    public function setLastChannelId(int $id)
    {
        $this->lastChannelId = $id;
    }

    public function makeChannelId(): int
    {
        return parent::makeChannelId();
    }
}
