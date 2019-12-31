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

namespace HyperfTest\JsonRpc;

use Hyperf\Config\Config;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandlerDispatcher;
use Hyperf\JsonRpc\Exception\Handler\HttpExceptionHandler;
use Hyperf\JsonRpc\TcpServer;
use Hyperf\Rpc\ProtocolManager;
use Hyperf\RpcServer\RequestDispatcher;
use Hyperf\Utils\ApplicationContext;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class TcpServerTest extends TestCase
{
    public function testGetDefaultExceptionHandler()
    {
        $container = $this->getContainer();
        $server = new TcpServer(
            $container,
            $container->get(RequestDispatcher::class),
            $container->get(ExceptionHandlerDispatcher::class),
            $container->get(ProtocolManager::class),
            $container->get(StdoutLoggerInterface::class)
        );

        $ref = new \ReflectionClass($server);
        $method = $ref->getMethod('getDefaultExceptionHandler');
        $method->setAccessible(true);
        $res = $method->invoke($server);

        $this->assertSame([HttpExceptionHandler::class], $res);
    }

    protected function getContainer()
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        $container->shouldReceive('get')->with(RequestDispatcher::class)->andReturn(new RequestDispatcher($container));
        $container->shouldReceive('get')->with(ExceptionHandlerDispatcher::class)->andReturn(new ExceptionHandlerDispatcher($container));
        $config = new Config([]);
        $container->shouldReceive('get')->with(ProtocolManager::class)->andReturn(new ProtocolManager($config));
        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturn(Mockery::mock(StdoutLoggerInterface::class));

        return $container;
    }
}
