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
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\PutMapping;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\HttpServer\Router\Handler;
use HyperfTest\HttpServer\Stub\DemoController;
use HyperfTest\HttpServer\Stub\DispatcherFactory;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class DispatcherFactoryTest extends TestCase
{
    public function testGetPrefix()
    {
        $factory = new DispatcherFactory();

        $res = $factory->getPrefix('App\Controller\Admin\UserController', '');
        $this->assertSame('/admin/user', $res);

        $res = $factory->getPrefix('App\Controller\Admin\UserAuthController', '');
        $this->assertSame('/admin/user_auth', $res);
    }

    public function testRemoveMagicMethods()
    {
        $factory = new DispatcherFactory();
        $annotation = new AutoController('test');
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
        $annotation = new AutoController('test', options: ['name' => 'Hyperf']);
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

    public function testHandleControllerWithSlash()
    {
        $factory = new DispatcherFactory();
        $annotation = new Controller('/');
        $methodMetadata = [
            'demo' => [
                GetMapping::class => new GetMapping(''),
                PostMapping::class => new PostMapping('demo2'),
                PutMapping::class => new PutMapping('/demo'),
            ],
        ];

        $factory->handleController(DemoController::class, $annotation, $methodMetadata);
        $router = $factory->getRouter('http');

        [$routers] = $router->getData();
        $this->assertSame('/demo', $routers['PUT']['/demo']->route);
        $this->assertSame('/demo2', $routers['POST']['/demo2']->route);
        $this->assertSame('/', $routers['GET']['/']->route);
    }

    public function testDispatchedHandlerIsNull()
    {
        $dispatched = new Dispatched([Dispatcher::NOT_FOUND]);
        $this->assertNull($dispatched->handler);
    }

    public function testMappingPathIsNullInController()
    {
        $factory = new DispatcherFactory();
        $annotation = new Controller('test');
        $methodMetadata = [
            'demo' => [
                GetMapping::class => new GetMapping(''),
                PostMapping::class => new PostMapping(),
                PutMapping::class => new PutMapping('/demo'),
            ],
            'userInfo' => [
                GetMapping::class => new GetMapping(),
            ],
            'BookInfo' => [
                GetMapping::class => new GetMapping(),
            ],
        ];

        $factory->handleController(DemoController::class, $annotation, $methodMetadata);
        $router = $factory->getRouter('http');

        [$routers] = $router->getData();

        // The routers array must contain the GET and PUT key.
        $this->assertArrayHasKey('GET', $routers);
        $this->assertArrayHasKey('PUT', $routers);
        // When the path in the old logic is null, the routers array does not contain the POST key.
        $this->assertArrayHasKey('POST', $routers);

        foreach ($routers as $method => $items) {
            /** @var Handler $value */
            foreach ($items as $value) {
                if ($method === 'GET') {
                    // When the path is an empty string, the route contains only the prefix.
                    $methodValue = $value->callback[1];
                    $expected = match ($methodValue) {
                        'demo' => '/test',
                        'userInfo' => '/test/user_info',
                        'BookInfo' => '/test/book_info',
                    };
                    $this->assertSame($expected, $value->route);
                } elseif ($method === 'POST') {
                    // When the path is null, the route is prefix + method name.
                    $this->assertSame('/test/demo', $value->route);
                    $this->assertSame([DemoController::class, 'demo'], $value->callback);
                } elseif ($method === 'PUT') {
                    // When the path contains the "/" character, the route is path
                    $this->assertSame('/demo', $value->route);
                    $this->assertSame([DemoController::class, 'demo'], $value->callback);
                }
            }
        }
    }
}
