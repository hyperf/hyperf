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

use Hyperf\Contract\ContainerInterface;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Router\RouteCollector;
use Hyperf\Utils\ApplicationContext;
use HyperfTest\HttpServer\Stub\DemoController;
use HyperfTest\HttpServer\Stub\DispatcherFactory;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 * @coversNothing
 */
class DispatcherFactoryTest extends TestCase
{
    protected function tearDown()
    {
        Mockery::close();
    }

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
        $this->getContainer();
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

    protected function getContainer()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('make')->with(RouteCollector::class)->withAnyArgs()->andReturnUsing(function ($_, $params) {
            $dispatcher = Mockery::mock(EventDispatcherInterface::class);
            return new RouteCollector($dispatcher, ...array_values($params));
        });

        ApplicationContext::setContainer($container);
        return $container;
    }
}
