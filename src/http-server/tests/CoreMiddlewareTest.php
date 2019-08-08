<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\HttpServer;

use Hyperf\Contract\NormalizerInterface;
use Hyperf\Di\MethodDefinitionCollector;
use Hyperf\Di\MethodDefinitionCollectorInterface;
use Hyperf\HttpServer\CoreMiddleware;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\Utils\Contracts\Arrayable;
use Hyperf\Utils\Contracts\Jsonable;
use Hyperf\Utils\Serializer\SimpleNormalizer;
use HyperfTest\HttpServer\Stub\CoreMiddlewareStub;
use HyperfTest\HttpServer\Stub\DemoController;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionMethod;

/**
 * @internal
 * @coversNothing
 */
class CoreMiddlewareTest extends TestCase
{
    public function testParseParameters()
    {
        $middleware = new CoreMiddlewareStub($container = $this->getContainer(), 'http');
        $id = rand(0, 99999);

        $params = $middleware->parseParameters(DemoController::class, 'index', ['id' => $id]);

        $this->assertSame([$id, 'Hyperf', []], $params);
    }

    public function testTransferToResponse()
    {
        $middleware = new CoreMiddlewareStub($container = $this->getContainer(), 'http');
        $reflectionMethod = new ReflectionMethod(CoreMiddleware::class, 'transferToResponse');
        $reflectionMethod->setAccessible(true);
        $request = Mockery::mock(ServerRequestInterface::class);
        /** @var ResponseInterface $response */

        // String
        $response = $reflectionMethod->invoke($middleware, $body = 'foo', $request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame($body, $response->getBody()->getContents());
        $this->assertSame('text/plain', $response->getHeaderLine('content-type'));

        // Array
        $response = $reflectionMethod->invoke($middleware, $body = ['foo' => 'bar'], $request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(json_encode($body), $response->getBody()->getContents());
        $this->assertSame('application/json', $response->getHeaderLine('content-type'));

        // Arrayable
        $response = $reflectionMethod->invoke($middleware, new class() implements Arrayable {
            public function toArray(): array
            {
                return ['foo' => 'bar'];
            }
        }, $request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(json_encode(['foo' => 'bar']), $response->getBody()->getContents());
        $this->assertSame('application/json', $response->getHeaderLine('content-type'));

        // Jsonable
        $response = $reflectionMethod->invoke($middleware, new class() implements Jsonable {
            public function __toString(): string
            {
                return json_encode(['foo' => 'bar'], JSON_UNESCAPED_UNICODE);
            }
        }, $request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(json_encode(['foo' => 'bar']), $response->getBody()->getContents());
        $this->assertSame('application/json', $response->getHeaderLine('content-type'));

        // __toString
        $response = $reflectionMethod->invoke($middleware, new class() {
            public function __toString(): string
            {
                return 'This is a string';
            }
        }, $request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame('This is a string', $response->getBody()->getContents());
        $this->assertSame('text/plain', $response->getHeaderLine('content-type'));
    }

    protected function getContainer()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with(DispatcherFactory::class)->andReturn(new DispatcherFactory());
        $container->shouldReceive('get')->with(MethodDefinitionCollectorInterface::class)
            ->andReturn(new MethodDefinitionCollector());
        $container->shouldReceive('get')->with(NormalizerInterface::class)
            ->andReturn(new SimpleNormalizer());
        return $container;
    }
}
