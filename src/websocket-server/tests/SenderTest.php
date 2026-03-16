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

namespace HyperfTest\WebSocketServer;

use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Server\CoroutineServer;
use Hyperf\WebSocketServer\Sender;
use HyperfTest\ModelCache\Stub\StdoutLogger;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Swoole\Server;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class SenderTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testSenderCheck()
    {
        $container = $this->getContainer();
        $server = Mockery::mock(Server::class);
        $server->shouldReceive('connection_info')->once()->andReturn(false);
        $server->shouldReceive('connection_info')->once()->andReturn([]);
        $server->shouldReceive('connection_info')->once()->andReturn(['websocket_status' => WEBSOCKET_STATUS_CLOSING]);
        $server->shouldReceive('connection_info')->once()->andReturn(['websocket_status' => WEBSOCKET_STATUS_ACTIVE]);
        $container->shouldReceive('get')->with(Server::class)->andReturn($server);
        $sender = new Sender($container);

        $this->assertFalse($sender->check(1));
        $this->assertFalse($sender->check(1));
        $this->assertFalse($sender->check(1));
        $this->assertTrue($sender->check(1));
    }

    public function testSenderResult()
    {
        $container = $this->getContainer([
            'server' => [
                'type' => CoroutineServer::class,
            ],
        ]);
        $sender = new Sender($container);
        $sender->setResponse(1, new class {
            public function push($data)
            {
                return true;
            }

            public function close()
            {
                return true;
            }
        });

        $res = $sender->push(1, 'data');
        $this->assertTrue($res);
        $res = $sender->disconnect(1);
        $this->assertTrue($res);

        $res = $sender->push(2, 'data');
        $this->assertFalse($res);
        $res = $sender->disconnect(2);
        $this->assertFalse($res);
    }

    protected function getContainer(array $config = [])
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturn(new StdoutLogger());
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new Config($config));

        return $container;
    }
}
