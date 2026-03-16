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

namespace HyperfTest\JsonRpc;

use Hyperf\Codec\Json;
use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\NormalizerInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\ClosureDefinitionCollectorInterface;
use Hyperf\Di\Container;
use Hyperf\Di\MethodDefinitionCollectorInterface;
use Hyperf\ExceptionHandler\ExceptionHandlerDispatcher;
use Hyperf\HttpMessage\Server\Request;
use Hyperf\JsonRpc\DataFormatter;
use Hyperf\JsonRpc\Exception\Handler\TcpExceptionHandler;
use Hyperf\JsonRpc\JsonRpcTransporter;
use Hyperf\JsonRpc\Packer\JsonEofPacker;
use Hyperf\JsonRpc\PathGenerator;
use Hyperf\JsonRpc\ResponseBuilder;
use Hyperf\JsonRpc\TcpServer;
use Hyperf\Rpc\Context;
use Hyperf\Rpc\ProtocolManager;
use Hyperf\RpcServer\RequestDispatcher;
use Hyperf\RpcServer\Router\DispatcherFactory;
use Hyperf\Serializer\SimpleNormalizer;
use Hyperf\Server\Event;
use Hyperf\Server\Server;
use Hyperf\Server\ServerFactory;
use Hyperf\Server\ServerManager;
use Hyperf\Stringable\Str;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use ReflectionClass;
use stdClass;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
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

        $ref = new ReflectionClass($server);
        $method = $ref->getMethod('getDefaultExceptionHandler');
        $res = $method->invoke($server);

        $this->assertSame([TcpExceptionHandler::class], $res);
    }

    public function testTcpServerBuildRequest()
    {
        $container = $this->getContainer();
        $server = new TcpServer(
            $container,
            $container->get(RequestDispatcher::class),
            $container->get(ExceptionHandlerDispatcher::class),
            $container->get(ProtocolManager::class),
            $container->get(StdoutLoggerInterface::class)
        );

        ServerManager::set('jsonrpc-tcp-test', [0, $port = new stdClass()]);
        $port->host = '0.0.0.0';
        $port->port = 9504;

        $server->initCoreMiddleware('jsonrpc-tcp-test');

        $ref = new ReflectionClass($server);
        $method = $ref->getMethod('buildRequest');
        /** @var Request $request */
        $request = $method->invoke($server, 1, 1, Json::encode([
            'jsonrpc' => '2.0',
            'method' => 'user/login',
            'params' => [
                'id' => $id = uniqid(),
                'name' => $name = Str::random(6),
            ],
            'id' => uniqid(),
            'context' => [
                'id' => $cid = uniqid(),
            ],
        ]));

        $this->assertSame([
            'id' => $id,
            'name' => $name,
        ], $request->getParsedBody());
        $this->assertSame('POST', $request->getMethod());

        $context = new Context();
        $this->assertSame($cid, $context->get('id'));
    }

    protected function getContainer()
    {
        $container = Mockery::mock(Container::class);
        ApplicationContext::setContainer($container);

        $container->shouldReceive('get')->with(RequestDispatcher::class)->andReturn(new RequestDispatcher($container));
        $container->shouldReceive('get')->with(ExceptionHandlerDispatcher::class)->andReturn(new ExceptionHandlerDispatcher($container));
        $config = new Config([
            'server' => [
                'servers' => [
                    [
                        'name' => 'jsonrpc-tcp-test',
                        'type' => Server::SERVER_BASE,
                        'host' => '0.0.0.0',
                        'port' => 9504,
                        'sock_type' => SWOOLE_SOCK_TCP,
                        'callbacks' => [
                            Event::ON_RECEIVE => [TcpServer::class, 'onReceive'],
                        ],
                        'settings' => [
                            'open_eof_split' => true,
                            'package_eof' => "\r\n",
                        ],
                    ],
                ],
            ],
        ]);
        $container->shouldReceive('get')->with(ProtocolManager::class)->andReturnUsing(function () use ($config) {
            $manager = new ProtocolManager($config);
            $manager->register('jsonrpc', [
                'packer' => JsonEofPacker::class,
                'transporter' => JsonRpcTransporter::class,
                'path-generator' => PathGenerator::class,
                'data-formatter' => DataFormatter::class,
            ]);
            return $manager;
        });
        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturn(Mockery::mock(StdoutLoggerInterface::class));
        $container->shouldReceive('get')->with(Context::class)->andReturn($context = new Context());
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn($config);
        $container->shouldReceive('has')->andReturn(true);
        $container->shouldReceive('make')->with(JsonEofPacker::class, Mockery::any())->andReturn(new JsonEofPacker());
        $container->shouldReceive('get')->with(DataFormatter::class)->andReturn(new DataFormatter($context));
        $container->shouldReceive('get')->with(PathGenerator::class)->andReturn(new PathGenerator());
        $container->shouldReceive('make')->with(ResponseBuilder::class, Mockery::any())->andReturnUsing(function ($_, $args) {
            return new ResponseBuilder(...array_values($args));
        });
        $container->shouldReceive('make')->with(DispatcherFactory::class, Mockery::any())->andReturnUsing(function ($_, $args) {
            return new DispatcherFactory(Mockery::mock(EventDispatcherInterface::class), ...array_values($args));
        });
        $container->shouldReceive('get')->with(NormalizerInterface::class)->andReturn(new SimpleNormalizer());
        $container->shouldReceive('get')->with(MethodDefinitionCollectorInterface::class)->andReturnUsing(function () {
            return Mockery::mock(MethodDefinitionCollectorInterface::class);
        });
        $container->shouldReceive('get')->with(ClosureDefinitionCollectorInterface::class)->andReturn(null);

        $dispatcher = Mockery::mock(EventDispatcherInterface::class);
        $dispatcher->shouldReceive('dispatch')->andReturn(true);
        $container->shouldReceive('has')->with(EventDispatcherInterface::class)->andReturn(true);
        $container->shouldReceive('get')->with(EventDispatcherInterface::class)->andReturn($dispatcher);

        $serverFactory = Mockery::mock(ServerFactory::class);
        $serverFactory->shouldReceive('getConfig')->andReturn(null);
        $container->shouldReceive('get')->with(ServerFactory::class)->andReturn($serverFactory);

        return $container;
    }
}
