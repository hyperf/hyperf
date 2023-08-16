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
namespace HyperfTest\Resource;

use FastRoute\Dispatcher;
use Hyperf\Context\Context;
use Hyperf\Contract\NormalizerInterface;
use Hyperf\Di\ClosureDefinitionCollector;
use Hyperf\Di\ClosureDefinitionCollectorInterface;
use Hyperf\Di\MethodDefinitionCollector;
use Hyperf\Di\MethodDefinitionCollectorInterface;
use Hyperf\HttpMessage\Server\Response;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\CoreMiddleware as HttpServerCore;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\HttpServer\Router\Handler;
use Hyperf\Serializer\SimpleNormalizer;
use Mockery;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @internal
 * @coversNothing
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        Context::set(ResponseInterface::class, new Response());
    }

    protected function tearDown(): void
    {
        Context::set(RequestInterface::class, null);
        Context::set(ResponseInterface::class, null);
        Mockery::close();
    }

    public function container()
    {
        defined('BASE_PATH') ?: define('BASE_PATH', __DIR__ . '/../');

        $container = Mockery::mock(ContainerInterface::class);

        $container->shouldReceive(...['get'])
            ->with(...[DispatcherFactory::class])
            ->andReturn(...[new DispatcherFactory()]);

        $container->shouldReceive(...['get'])
            ->with(...[MethodDefinitionCollectorInterface::class])
            ->andReturn(...[new MethodDefinitionCollector()]);

        $container->shouldReceive(...['has'])
            ->with(...[ClosureDefinitionCollectorInterface::class])
            ->andReturn(...[false]);

        $container->shouldReceive(...['get'])
            ->with(...[ClosureDefinitionCollectorInterface::class])
            ->andReturn(...[new ClosureDefinitionCollector()]);

        $container->shouldReceive(...['get'])
            ->with(...[NormalizerInterface::class])
            ->andReturn(...[new SimpleNormalizer()]);

        return $container;
    }

    /**
     * @param array|callable|string $except
     * @return HttpResponse
     */
    public function http($except)
    {
        $core = new HttpServerCore($container = $this->container(), 'http');

        $handle = Mockery::mock(RequestHandlerInterface::class);

        $request = Mockery::mock(ServerRequestInterface::class);

        $request->shouldReceive(...['getAttribute'])
            ->with(...[Dispatched::class])
            ->andReturn(...[new Dispatched([
                Dispatcher::FOUND,
                new Handler($except, '*'),
                [],
            ])]);

        return HttpResponse::fromBaseResponse($core->process($request, $handle));
    }
}
