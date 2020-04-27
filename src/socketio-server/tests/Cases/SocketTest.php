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
use Hyperf\SocketIOServer\Room\AdapterInterface;
use Hyperf\SocketIOServer\Socket;
use Hyperf\Utils\ApplicationContext;
use Hyperf\WebSocketServer\Sender;
use Mockery;

/**
 * @internal
 * @coversNothing
 */
class SocketTest extends AbstractTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->getContainer();
    }

    public function testJoin()
    {
        /** @var Socket $socket */
        $socket = make(Socket::class, [
            'fd' => 1,
            'nsp' => '/',
        ]);
        $socket->join('room');
        $adapter = ApplicationContext::getContainer()->get(AdapterInterface::class);
        $this->assertEquals(['room'], $adapter->clientRooms($socket->getSid()));
    }

    public function testLeave()
    {
        /** @var Socket $socket */
        $socket = make(Socket::class, [
            'fd' => 1,
            'nsp' => '/',
        ]);
        $socket->join('room', 'another_room');
        $socket->leave('room');
        $adapter = ApplicationContext::getContainer()->get(AdapterInterface::class);
        $this->assertEquals(['another_room'], $adapter->clientRooms($socket->getSid()));
    }

    public function testLeaveAll()
    {
        /** @var Socket $socket */
        $socket = make(Socket::class, [
            'fd' => 1,
            'nsp' => '/',
        ]);
        $socket->join('room', 'room2', 'room3');
        $socket->leaveAll();
        $adapter = ApplicationContext::getContainer()->get(AdapterInterface::class);
        $this->assertEquals([], $adapter->clientRooms($socket->getSid()));
    }

    public function testTo()
    {
        /** @var ContainerInterface $container */
        $container = ApplicationContext::getContainer();
        $mock = Mockery::mock(Sender::class);
        $mock->shouldNotReceive('push')->with(1, Mockery::any(), Mockery::any(), Mockery::any());
        $mock->shouldReceive('push')->with(2, Mockery::any(), Mockery::any(), Mockery::any())->once();
        $mock->shouldReceive('push')->with(3, Mockery::any(), Mockery::any(), Mockery::any())->once();
        $container->set(Sender::class, $mock);
        /** @var Socket $socket1 */
        $socket1 = make(Socket::class, [
            'fd' => 1,
            'nsp' => '/',
        ]);
        /** @var Socket $socket2 */
        $socket2 = make(Socket::class, [
            'fd' => 2,
            'nsp' => '/',
        ]);
        /** @var Socket $socket3 */
        $socket3 = make(Socket::class, [
            'fd' => 3,
            'nsp' => '/',
        ]);
        $socket1->join('room');
        $socket2->join('room');
        $socket3->join('room');
        $socket1->to('room')->emit('hello');
        $this->assertTrue(true);
    }
}
