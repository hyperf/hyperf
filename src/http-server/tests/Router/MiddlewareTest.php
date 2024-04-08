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

use FastRoute\Dispatcher\GroupCountBased;
use Hyperf\Di\Annotation\MultipleAnnotation;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\MiddlewareManager;
use Hyperf\HttpServer\PriorityMiddleware;
use Hyperf\HttpServer\Router\Handler;
use HyperfTest\HttpServer\Stub\BarMiddleware;
use HyperfTest\HttpServer\Stub\DemoController;
use HyperfTest\HttpServer\Stub\DispatcherFactory;
use HyperfTest\HttpServer\Stub\FooMiddleware;
use HyperfTest\HttpServer\Stub\SetHeaderMiddleware;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class MiddlewareTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        MiddlewareManager::$container = [];
    }

    public function testMiddlewareInController()
    {
        $factory = new DispatcherFactory();
        // Middleware in options should not works.
        $annotation = new Controller('test', options: ['name' => 'Hyperf', 'middleware' => [BarMiddleware::class]]);
        $factory->handleController(
            DemoController::class,
            $annotation,
            ['index' => [
                GetMapping::class => new GetMapping('/index', ['name' => 'index.get', 'id' => 1]),
                PostMapping::class => new PostMapping('/index', ['name' => 'index.post']),
                Middleware::class => new MultipleAnnotation(new Middleware(FooMiddleware::class)),
            ]],
            [new PriorityMiddleware(SetHeaderMiddleware::class)]
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
                if ($method === 'GET') {
                    $this->assertSame(1, $value->options['id']);
                } else {
                    $this->assertArrayNotHasKey('id', $value->options);
                }

                foreach ([
                    $value->options['middleware'],
                    MiddlewareManager::get('http', $value->route, $method),
                ] as $dataSource) {
                    $this->assertMiddlewares([
                        SetHeaderMiddleware::class,
                        FooMiddleware::class,
                    ], $dataSource);
                }
            }
        }
    }

    public function testMiddlewarePriorityInController()
    {
        $factory = new DispatcherFactory();
        // Middleware in options should not works.
        $annotation = new Controller('test', options: ['name' => 'Hyperf', 'middleware' => [BarMiddleware::class]]);
        $factory->handleController(
            DemoController::class,
            $annotation,
            ['index' => [
                GetMapping::class => new GetMapping('/index', ['name' => 'index.get', 'id' => 1]),
                PostMapping::class => new PostMapping('/index', ['name' => 'index.post']),
                Middlewares::class => new Middlewares([
                    BarMiddleware::class => 1,
                    FooMiddleware::class => 3,
                ]),
            ]],
            [
                new PriorityMiddleware(SetHeaderMiddleware::class, 1),
                new PriorityMiddleware(FooMiddleware::class),
            ]
        );
        $router = $factory->getRouter('http');

        [$routers] = $router->getData();

        $globalMiddleware = [
            'GlobalMiddlewareA',
            'GlobalMiddlewareB' => 1,
        ];

        foreach ($routers as $method => $items) {
            /**
             * @var string $key
             * @var Handler $value
             */
            foreach ($items as $key => $value) {
                $this->assertSame([DemoController::class, 'index'], $value->callback);
                $this->assertSame('/index', $value->route);
                $this->assertSame('index.' . strtolower($method), $value->options['name']);
                if ($method === 'GET') {
                    $this->assertSame(1, $value->options['id']);
                } else {
                    $this->assertArrayNotHasKey('id', $value->options);
                }

                foreach ([
                    // Keep same with Server.php `$middlewares = array_merge($middlewares, $registeredMiddlewares);`
                    array_merge($globalMiddleware, $value->options['middleware']),
                    array_merge($globalMiddleware, MiddlewareManager::get('http', $value->route, $method)),
                ] as $dataSource) {
                    $this->assertMiddlewares([
                        FooMiddleware::class, // method middleware => 3
                        'GlobalMiddlewareB', // global middleware => 1
                        SetHeaderMiddleware::class, // class middleware => 1
                        BarMiddleware::class, // method middleware => 1
                        'GlobalMiddlewareA', // global middleware => 0
                    ], $dataSource);
                }
            }
        }
    }

    public function testFallbackForHead()
    {
        MiddlewareManager::addMiddlewares('http', '/index', 'GET', [FooMiddleware::class]);
        MiddlewareManager::addMiddlewares('http', '/head-register', 'HEAD', []);

        $grouopCountBased = new GroupCountBased([
            [
                'GET' => [
                    '/index' => 'index::handler',
                ],
                'HEAD' => [
                    '/head-register' => 'head-register::handler',
                ],
            ],
        ]);
        $this->assertSame([
            GroupCountBased::FOUND,
            'index::handler',
            [],
        ], $grouopCountBased->dispatch('GET', '/index'));
        $this->assertSame([
            GroupCountBased::FOUND,
            'index::handler',
            [],
        ], $grouopCountBased->dispatch('HEAD', '/index'));
        $this->assertSame([
            GroupCountBased::FOUND,
            'head-register::handler',
            [],
        ], $grouopCountBased->dispatch('HEAD', '/head-register'));

        $this->assertSame([FooMiddleware::class], MiddlewareManager::get('http', '/index', 'GET'));
        $this->assertSame([FooMiddleware::class], MiddlewareManager::get('http', '/index', 'HEAD'));
        $this->assertSame([], MiddlewareManager::get('http', '/head-register', 'GET'));
    }

    /**
     * @param string[] $expectMiddlewares
     */
    protected function assertMiddlewares(array $expectMiddlewares, array $middlewares)
    {
        $middlewares = MiddlewareManager::sortMiddlewares($middlewares);

        $offset = 0;
        foreach ($middlewares as $middlewareKey => $middleware) {
            if ($middleware instanceof PriorityMiddleware) {
                $this->assertSame($middleware->middleware, $expectMiddlewares[$offset] ?? '');
            } elseif (is_int($middleware)) {
                $middleware = $middlewareKey;
                $this->assertSame($middleware, $expectMiddlewares[$offset] ?? '');
            } else {
                $this->assertSame($middleware, $expectMiddlewares[$offset] ?? '');
            }
            ++$offset;
        }
    }
}
