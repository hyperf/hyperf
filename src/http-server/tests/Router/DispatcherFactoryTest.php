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
namespace HyperfTest\HttpServer\Router;

use FastRoute\Dispatcher;
use Hyperf\Di\Annotation\MultipleAnnotation;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\HttpServer\Router\Handler;
use HyperfTest\HttpServer\Stub\BarMiddleware;
use HyperfTest\HttpServer\Stub\DemoController;
use HyperfTest\HttpServer\Stub\DispatcherFactory;
use HyperfTest\HttpServer\Stub\FooMiddleware;
use HyperfTest\HttpServer\Stub\SetHeaderMiddleware;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class DispatcherFactoryTest extends TestCase
{
    public function testGetPrefix()
    {
        $factory = new DispatcherFactory();

        $res = $factory->getPrefix('App\\Controller\\Admin\\UserController', '');
        $this->assertSame('/admin/user', $res);

        $res = $factory->getPrefix('App\\Controller\\Admin\\UserAuthController', '');
        $this->assertSame('/admin/user_auth', $res);
    }

    public function testRemoveMagicMethods()
    {
        $factory = new DispatcherFactory();
        $annotation = new AutoController(['prefix' => 'test']);
        $factory->handleAutoController(DemoController::class, $annotation);

        $router = $factory->getRouter('http');

        [$routers] = $router->getData();

        $this->assertSame(['GET', 'POST', 'HEAD'], array_keys($routers));
        foreach ($routers as $method => $items) {
            $this->assertFalse(in_array('/test/__construct', array_keys($items)));
            $this->assertFalse(in_array('/test/__return', array_keys($items)));
        }
    }

    public function testOptionsInAutoController()
    {
        $factory = new DispatcherFactory();
        $annotation = new AutoController(['prefix' => 'test', 'options' => ['name' => 'Hyperf']]);
        $factory->handleAutoController(DemoController::class, $annotation);
        $router = $factory->getRouter('http');

        [$routers] = $router->getData();
        foreach ($routers as $method => $items) {
            /**
             * @var string $key
             * @var Handler $value
             */
            foreach ($items as $key => $value) {
                $this->assertSame('Hyperf', $value->options['name']);
            }
        }
    }

    public function testMiddlewareInController()
    {
        $factory = new DispatcherFactory();
        // Middleware in options should not works.
        $annotation = new Controller(['prefix' => 'test', 'options' => ['name' => 'Hyperf', 'middleware' => [BarMiddleware::class]]]);
        $factory->handleController(
            DemoController::class,
            $annotation,
            ['index' => [
                GetMapping::class => new GetMapping(['path' => '/index', 'options' => ['name' => 'index.get', 'id' => 1]]),
                PostMapping::class => new PostMapping(['path' => '/index', 'options' => ['name' => 'index.post']]),
                Middleware::class => new MultipleAnnotation(new Middleware(['middleware' => FooMiddleware::class])),
            ]],
            [SetHeaderMiddleware::class]
        );
        $router = $factory->getRouter('http');

        [$routers] = $router->getData();
        foreach ($routers as $method => $items) {
            /**
             * @var string $key
             * @var Handler $value
             */
            foreach ($items as $key => $value) {
                $this->assertSame([DemoController::class, 'index'], $value->callback);
                $this->assertSame('/index', $value->route);
                $this->assertSame('index.' . strtolower($method), $value->options['name']);
                $this->assertSame([
                    SetHeaderMiddleware::class,
                    FooMiddleware::class,
                ], $value->options['middleware']);
                if ($method === 'GET') {
                    $this->assertSame(1, $value->options['id']);
                } else {
                    $this->assertArrayNotHasKey('id', $value->options);
                }
            }
        }
    }

    public function testDispatchedHandlerIsNull()
    {
        $dispatched = new Dispatched([Dispatcher::NOT_FOUND]);
        $this->assertNull($dispatched->handler);
    }
}
