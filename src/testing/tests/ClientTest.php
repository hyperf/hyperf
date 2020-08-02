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
namespace HyperfTest\Testing;

use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\NormalizerInterface;
use Hyperf\Di\ClosureDefinitionCollectorInterface;
use Hyperf\Di\Container;
use Hyperf\Di\MethodDefinitionCollector;
use Hyperf\Di\MethodDefinitionCollectorInterface;
use Hyperf\Dispatcher\HttpDispatcher;
use Hyperf\ExceptionHandler\ExceptionHandlerDispatcher;
use Hyperf\HttpServer\CoreMiddleware;
use Hyperf\HttpServer\ResponseEmitter;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\HttpServer\Router\Router;
use Hyperf\Testing\Client;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Filesystem\Filesystem;
use Hyperf\Utils\Serializer\SimpleNormalizer;
use HyperfTest\Testing\Stub\Exception\Handler\FooExceptionHandler;
use HyperfTest\Testing\Stub\FooController;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ClientTest extends TestCase
{
    public function testClientRequest()
    {
        $container = $this->getContainer();

        $client = new Client($container);

        $data = $client->get('/');

        $this->assertSame(0, $data['code']);
        $this->assertSame('Hello Hyperf!', $data['data']);
    }

    public function testClientException()
    {
        $container = $this->getContainer();

        $client = new Client($container);

        $data = $client->get('/exception');

        $this->assertSame(500, $data['code']);
        $this->assertSame('Server Error', $data['message']);
    }

    public function getContainer()
    {
        $container = Mockery::mock(Container::class);

        $container->shouldReceive('get')->with(HttpDispatcher::class)->andReturn(new HttpDispatcher($container));
        $container->shouldReceive('get')->with(ExceptionHandlerDispatcher::class)->andReturn(new ExceptionHandlerDispatcher($container));
        $container->shouldReceive('get')->with(ResponseEmitter::class)->andReturn(new ResponseEmitter());
        $container->shouldReceive('get')->with(DispatcherFactory::class)->andReturn($factory = new DispatcherFactory());
        $container->shouldReceive('get')->with(NormalizerInterface::class)->andReturn(new SimpleNormalizer());
        $container->shouldReceive('get')->with(MethodDefinitionCollectorInterface::class)->andReturn(new MethodDefinitionCollector());
        $container->shouldReceive('has')->with(ClosureDefinitionCollectorInterface::class)->andReturn(false);
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new Config([
            'exceptions' => [
                'handler' => [
                    'http' => [
                        FooExceptionHandler::class,
                    ],
                ],
            ],
        ]));
        $container->shouldReceive('get')->with(Filesystem::class)->andReturn(new Filesystem());
        $container->shouldReceive('get')->with(FooController::class)->andReturn(new FooController());
        $container->shouldReceive('has')->andReturn(true);
        $container->shouldReceive('get')->with(FooExceptionHandler::class)->andReturn(new FooExceptionHandler());
        $container->shouldReceive('make')->with(CoreMiddleware::class, Mockery::any())->andReturnUsing(function ($class, $args) {
            return new CoreMiddleware(...array_values($args));
        });
        ApplicationContext::setContainer($container);

        Router::init($factory);
        Router::get('/', [FooController::class, 'index']);
        Router::get('/exception', [FooController::class, 'exception']);

        return $container;
    }
}
