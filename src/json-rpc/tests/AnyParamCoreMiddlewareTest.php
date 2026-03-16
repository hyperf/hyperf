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

use Error;
use Hyperf\Codec\Packer\JsonPacker;
use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\NormalizerInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\ClosureDefinitionCollectorInterface;
use Hyperf\Di\Container;
use Hyperf\Di\MethodDefinitionCollector;
use Hyperf\Di\MethodDefinitionCollectorInterface;
use Hyperf\HttpMessage\Base\Response;
use Hyperf\HttpMessage\Server\Request;
use Hyperf\HttpMessage\Uri\Uri;
use Hyperf\JsonRpc\CoreMiddleware;
use Hyperf\JsonRpc\DataFormatter;
use Hyperf\JsonRpc\JsonRpcHttpTransporter;
use Hyperf\JsonRpc\JsonRpcTransporter;
use Hyperf\JsonRpc\NormalizeDataFormatter;
use Hyperf\JsonRpc\Packer\JsonEofPacker;
use Hyperf\JsonRpc\PathGenerator;
use Hyperf\JsonRpc\ResponseBuilder;
use Hyperf\Logger\Logger;
use Hyperf\Rpc\Context as RpcContext;
use Hyperf\Rpc\Protocol;
use Hyperf\Rpc\ProtocolManager;
use Hyperf\RpcServer\RequestDispatcher;
use Hyperf\RpcServer\Router\DispatcherFactory;
use Hyperf\Serializer\SerializerFactory;
use Hyperf\Serializer\SymfonyNormalizer;
use HyperfTest\JsonRpc\Stub\CalculatorService;
use InvalidArgumentException;
use Mockery;
use Monolog\Handler\StreamHandler;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class AnyParamCoreMiddlewareTest extends TestCase
{
    public function testProcess()
    {
        $container = $this->createContainer();
        $router = $container->make(DispatcherFactory::class, [])->getRouter('jsonrpc');
        $router->addRoute('/CalculatorService/sum', [
            CalculatorService::class, 'sum',
        ]);
        $protocol = new Protocol($container, $container->get(ProtocolManager::class), 'jsonrpc');
        $builder = $container->make(ResponseBuilder::class, [
            'dataFormatter' => $protocol->getDataFormatter(),
            'packer' => $protocol->getPacker(),
        ]);
        $middleware = new CoreMiddleware($container, $protocol, $builder, 'jsonrpc');
        $handler = Mockery::mock(RequestHandlerInterface::class);
        $request = (new Request('POST', new Uri('/CalculatorService/sum')))
            ->withParsedBody([
                ['value' => 1],
                ['value' => 2],
            ]);

        $request = $middleware->dispatch($request);
        Context::set(ResponseInterface::class, new Response());

        $response = $middleware->process($request, $handler);
        $this->assertEquals(200, $response->getStatusCode());
        $ret = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('result', $ret);
        $this->assertEquals(['value' => 3], $ret['result']);
    }

    public function testArray()
    {
        $container = $this->createContainer();
        $router = $container->make(DispatcherFactory::class, [])->getRouter('jsonrpc');
        $router->addRoute('/CalculatorService/array', [
            CalculatorService::class, 'array',
        ]);
        $protocol = new Protocol($container, $container->get(ProtocolManager::class), 'jsonrpc');
        $builder = $container->make(ResponseBuilder::class, [
            'dataFormatter' => $protocol->getDataFormatter(),
            'packer' => $protocol->getPacker(),
        ]);
        $middleware = new CoreMiddleware($container, $protocol, $builder, 'jsonrpc');
        $handler = Mockery::mock(RequestHandlerInterface::class);
        $request = (new Request('POST', new Uri('/CalculatorService/array')))
            ->withParsedBody([1, 2]);

        $request = $middleware->dispatch($request);
        Context::set(ResponseInterface::class, new Response());

        $response = $middleware->process($request, $handler);
        $this->assertEquals(200, $response->getStatusCode());
        $ret = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('result', $ret);
        $this->assertEquals(['params' => [1, 2], 'sum' => 3], $ret['result']);
    }

    public function testException()
    {
        $container = $this->createContainer();
        $router = $container->make(DispatcherFactory::class, [])->getRouter('jsonrpc');
        $router->addRoute('/CalculatorService/divide', [
            CalculatorService::class, 'divide',
        ]);
        $protocol = new Protocol($container, $container->get(ProtocolManager::class), 'jsonrpc');
        $builder = $container->make(ResponseBuilder::class, [
            'dataFormatter' => $protocol->getDataFormatter(),
            'packer' => $protocol->getPacker(),
        ]);
        $middleware = new CoreMiddleware($container, $protocol, $builder, 'jsonrpc');
        $handler = Mockery::mock(RequestHandlerInterface::class);
        $request = (new Request('POST', new Uri('/CalculatorService/divide')))
            ->withParsedBody([3, 0]);
        Context::set(ResponseInterface::class, new Response());

        $request = $middleware->dispatch($request);
        try {
            $response = $middleware->process($request, $handler);
        } catch (Throwable $exception) {
            $response = Context::get(ResponseInterface::class);
        }
        $this->assertEquals(200, $response->getStatusCode());
        $ret = json_decode((string) $response->getBody(), true);

        $this->assertArrayHasKey('error', $ret);
        $this->assertArrayHasKey('data', $ret['error']);

        $this->assertEquals(InvalidArgumentException::class, $ret['error']['data']['class']);
        $this->assertSame('Expected non-zero value of divider', $ret['error']['data']['attributes']['message']);
        $this->assertSame(0, $ret['error']['data']['attributes']['code']);
    }

    public function testThrowable()
    {
        $container = $this->createContainer();
        $router = $container->make(DispatcherFactory::class, [])->getRouter('jsonrpc');
        $router->addRoute('/CalculatorService/error', [
            CalculatorService::class, 'error',
        ]);
        $protocol = new Protocol($container, $container->get(ProtocolManager::class), 'jsonrpc');
        $builder = $container->make(ResponseBuilder::class, [
            'dataFormatter' => $protocol->getDataFormatter(),
            'packer' => $protocol->getPacker(),
        ]);
        $middleware = new CoreMiddleware($container, $protocol, $builder, 'jsonrpc');
        $handler = Mockery::mock(RequestHandlerInterface::class);
        $request = (new Request('POST', new Uri('/CalculatorService/error')))
            ->withParsedBody([]);
        Context::set(ResponseInterface::class, new Response());

        $request = $middleware->dispatch($request);
        try {
            $response = $middleware->process($request, $handler);
        } catch (Throwable $exception) {
            $response = Context::get(ResponseInterface::class);
        }
        $this->assertEquals(200, $response->getStatusCode());
        $ret = json_decode((string) $response->getBody(), true);

        $this->assertArrayHasKey('error', $ret);
        $this->assertArrayHasKey('data', $ret['error']);

        $this->assertEquals(Error::class, $ret['error']['data']['class']);
        $this->assertSame('Not only a exception.', $ret['error']['data']['attributes']['message']);
        $this->assertSame(0, $ret['error']['data']['attributes']['code']);
    }

    public function createContainer()
    {
        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $container = Mockery::mock(Container::class);
        $container->shouldReceive('get')->with(ConfigInterface::class)
            ->andReturn($config = new Config([
                'protocols' => [
                    'jsonrpc' => [
                        'packer' => JsonPacker::class,
                        'transporter' => JsonRpcTransporter::class,
                        'path-generator' => PathGenerator::class,
                        'data-formatter' => DataFormatter::class,
                    ],
                    'jsonrpc-http' => [
                        'packer' => JsonPacker::class,
                        'transporter' => JsonRpcHttpTransporter::class,
                        'path-generator' => PathGenerator::class,
                        'data-formatter' => DataFormatter::class,
                    ],
                ],
            ]));
        $container->shouldReceive('has')->andReturn(true);
        $container->shouldReceive('get')->with(ProtocolManager::class)
            ->andReturn(new ProtocolManager($config));
        $container->shouldReceive('get')->with(NormalizerInterface::class)
            ->andReturn($normalizer = new SymfonyNormalizer((new SerializerFactory())->__invoke()));
        $container->shouldReceive('get')->with(MethodDefinitionCollectorInterface::class)
            ->andReturn(new MethodDefinitionCollector());
        $container->shouldReceive('has')->with(ClosureDefinitionCollectorInterface::class)
            ->andReturn(false);
        $container->shouldReceive('get')->with(ClosureDefinitionCollectorInterface::class)
            ->andReturn(null);
        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)
            ->andReturn(new Logger('App', [new StreamHandler('php://stderr')]));
        $container->shouldReceive('get')->with(EventDispatcherInterface::class)
            ->andReturn($eventDispatcher);
        $container->shouldReceive('get')->with(PathGenerator::class)
            ->andReturn(new PathGenerator());
        $container->shouldReceive('get')->with(DataFormatter::class)
            ->andReturn(new NormalizeDataFormatter($normalizer, new RpcContext()));
        $container->shouldReceive('get')->with(JsonPacker::class)
            ->andReturn(new JsonPacker());
        $container->shouldReceive('get')->with(CalculatorService::class)
            ->andReturn(new CalculatorService());
        $container->shouldReceive('make')->with(DispatcherFactory::class, Mockery::any())
            ->andReturn(new DispatcherFactory($eventDispatcher, new PathGenerator()));
        $container->shouldReceive('make')->with(ResponseBuilder::class, Mockery::any())
            ->andReturnUsing(function ($class, $args) {
                return new ResponseBuilder(...array_values($args));
            });
        $container->shouldReceive('get')->with(RequestDispatcher::class)->andReturn(new RequestDispatcher($container));
        $container->shouldReceive('make')->with(JsonPacker::class, Mockery::any())->andReturn(new JsonPacker());
        $container->shouldReceive('make')->with(JsonEofPacker::class, Mockery::any())->andReturnUsing(function ($_, $args) {
            return new JsonEofPacker(...array_values($args));
        });
        $container->shouldReceive('get')->with(RpcContext::class)->andReturn(new RpcContext());

        ApplicationContext::setContainer($container);
        return $container;
    }
}
