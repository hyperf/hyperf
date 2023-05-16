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
namespace HyperfTest\SocketIOServer\Cases;

use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\SocketIOServer\BaseNamespace;
use Hyperf\SocketIOServer\Collector\SocketIORouter;
use Hyperf\SocketIOServer\Parser\Decoder;
use Hyperf\SocketIOServer\Parser\Encoder;
use Hyperf\SocketIOServer\Room\AdapterInterface;
use Hyperf\SocketIOServer\SidProvider\LocalSidProvider;
use Hyperf\SocketIOServer\SocketIO;
use Hyperf\WebSocketServer\Sender;
use HyperfTest\SocketIOServer\Stub\EphemeralAdapter;
use Mockery;
use ReflectionClass;
use Swoole\Atomic;

/**
 * @internal
 * @coversNothing
 */
class IONamespaceTest extends AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->getContainer();
    }

    public function testEmit()
    {
        $sender = Mockery::Spy(Sender::class);
        $sidProvider = new LocalSidProvider();
        $io = new BaseNamespace($sender, $sidProvider);
        $io->getAdapter()->add('1');
        $io->getAdapter()->add('2');
        $io->emit('hello', 'world');
        $sender->shouldHaveReceived('push')->twice();
        $this->assertTrue(true);
    }

    public function testGetNsp()
    {
        $sender = Mockery::Mock(Sender::class);
        $sidProvider = new LocalSidProvider();
        $io = new BaseNamespace($sender, $sidProvider);
        $this->assertEquals('/', $io->getNamespace());
    }

    public function testGetAdapter()
    {
        $sender = Mockery::Mock(Sender::class);
        $sidProvider = new LocalSidProvider();
        $io = new BaseNamespace($sender, $sidProvider);
        $this->assertInstanceOf(AdapterInterface::class, $io->getAdapter());
    }

    public function testEmitResponse()
    {
        $sender = Mockery::Spy(Sender::class);
        $sidProvider = new LocalSidProvider();
        $io = new BaseNamespace($sender, $sidProvider);
        SocketIORouter::addNamespace('/', BaseNamespace::class);
        SocketIO::$messageId = new Atomic();
        $io->getAdapter()->add('1');
        $io->getAdapter()->add('2');
        $io->emit('hello', 'world', true);
        $sender->shouldHaveReceived('push')->twice();
        $this->assertTrue(true);
    }

    public function testBroadcast()
    {
        SocketIO::$messageId = new Atomic();
        $sender = Mockery::Mock(Sender::class);
        $sender->shouldNotReceive('push')->withAnyArgs();
        $sidProvider = new LocalSidProvider();
        $io = new BaseNamespace($sender, $sidProvider);
        $io->broadcast->emit('hello', 'world', true);
        $this->assertTrue(true);
    }

    public function testNonExistRoom()
    {
        SocketIO::$messageId = new Atomic();
        $sender = Mockery::Mock(Sender::class);
        $sender->shouldNotReceive('push')->withAnyArgs();
        $sidProvider = new LocalSidProvider();
        $io = new BaseNamespace($sender, $sidProvider);
        $io->to('non-exist')->emit('hello', 'world', false);
        $this->assertTrue(true);
    }

    public function testExistRoom()
    {
        SocketIO::$messageId = new Atomic();
        $sender = Mockery::spy(Sender::class);
        $sidProvider = new LocalSidProvider();
        $io = new BaseNamespace($sender, $sidProvider);
        $io->getAdapter()->add('1', 'room');
        $io->getAdapter()->add('2', 'room');
        $io->to('room')->emit('hello', 'world', false);
        $sender->shouldHaveReceived('push')->withAnyArgs()->twice();
        $this->assertTrue(true);
    }

    public function testRenewInAllNamespaces()
    {
        $container = ApplicationContext::getContainer();
        $container->define(AdapterInterface::class, EphemeralAdapter::class);

        SocketIO::$messageId = new Atomic();
        $io = new SocketIO(
            Mockery::mock(StdoutLoggerInterface::class),
            Mockery::mock(Sender::class),
            Mockery::mock(Decoder::class),
            Mockery::mock(Encoder::class),
            $sidProvider = new LocalSidProvider()
        );

        SocketIORouter::addNamespace('/', BaseNamespace::class);

        $ref = new ReflectionClass($io);
        $m = $ref->getMethod('renewInAllNamespaces');
        $m->setAccessible(true);
        $this->assertFalse(EphemeralAdapter::$isRenew);
        $m->invokeArgs($io, [1]);
        $this->assertTrue(EphemeralAdapter::$isRenew);
    }
}
