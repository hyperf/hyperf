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
use Hyperf\Coroutine\Channel\Manager as ChannelManager;

class AMQPConnectionStub extends AMQPConnection
{
    public function __construct()
    {
        $this->channelManager = new ChannelManager(16);
        $this->channelManager->get(0, true);
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
