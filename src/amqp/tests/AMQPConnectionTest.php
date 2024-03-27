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

namespace HyperfTest\Amqp;

use Hyperf\Amqp\ConnectionFactory;
use Hyperf\Amqp\Message\Type;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use HyperfTest\Amqp\Stub\AMQPConnectionStub;
use HyperfTest\Amqp\Stub\ContainerStub;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class AMQPConnectionTest extends TestCase
{
    public function testMakeChannelId()
    {
        $connection = new AMQPConnectionStub();

        $this->assertSame(1, $connection->makeChannelId());
        $connection->channels[2] = 1;
        $this->assertSame(3, $connection->makeChannelId());
        $connection->setLastChannelId(10000);
        $this->assertSame(10001, $connection->makeChannelId());
        $connection->setLastChannelId(65534);
        $this->assertSame(65535, $connection->makeChannelId());
        $this->assertSame(1, $connection->makeChannelId());
        $this->assertSame(3, $connection->makeChannelId());
    }

    public function testConnectionClose()
    {
        $container = ContainerStub::getContainer();
        $connection = (new ConnectionFactory($container))->make([]);
        $channel = $connection->getChannel();
        $channel->exchange_declare('test', Type::TOPIC->value);
        $this->assertNull($connection->close());
    }

    public function testConnectionClosedByExit()
    {
        $container = ContainerStub::getContainer();
        $connection = (new ConnectionFactory($container))->make([
            'params' => [
                'heartbeat' => 1,
            ],
        ]);
        $channel = $connection->getChannel();
        $channel->exchange_declare('test', Type::TOPIC->value);

        CoordinatorManager::until(Constants::WORKER_EXIT)->resume();
        CoordinatorManager::clear(Constants::WORKER_EXIT);
        $this->assertTrue(true);
    }
}
