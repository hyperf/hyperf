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
namespace HyperfTest\Cases;

use Hyperf\Contract\ContainerInterface;
use Hyperf\SocketIOServer\Emitter\Future;
use Hyperf\Utils\ApplicationContext;
use Hyperf\WebSocketServer\Sender;
use Mockery;
use Swoole\Coroutine\Channel;

/**
 * @internal
 * @coversNothing
 */
class FutureTest extends AbstractTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->getContainer();
    }

    public function testDestruct()
    {
        /** @var ContainerInterface $container */
        $container = ApplicationContext::getContainer();
        $mock = Mockery::mock(Sender::class);
        $mock->shouldReceive('push')->with(1, Mockery::any(), Mockery::any(), Mockery::any())->once();
        $container->set(Sender::class, $mock);
        $future = make(Future::class, ['fd' => 1, 'data' => '', 'opcode' => 0, 'flag' => 0]);
        unset($future);
        $this->assertTrue(true);
    }

    public function testChannel()
    {
        /** @var ContainerInterface $container */
        $container = ApplicationContext::getContainer();
        $mock = Mockery::mock(Sender::class);
        $mock->shouldReceive('push')->with(1, Mockery::any(), Mockery::any(), Mockery::any())->once();
        $container->set(Sender::class, $mock);
        /** @var Future $future */
        $future = make(Future::class, ['fd' => 1, 'data' => '', 'opcode' => 0, 'flag' => 0]);
        $ch = $future->channel();
        $this->assertInstanceOf(Channel::class, $ch);
    }
}
