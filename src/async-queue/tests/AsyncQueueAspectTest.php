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
namespace HyperfTest\AsyncQueue;

use Hyperf\AsyncQueue\Annotation\AsyncQueueMessage;
use Hyperf\AsyncQueue\AnnotationJob;
use Hyperf\AsyncQueue\Aspect\AsyncQueueAspect;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\Driver\DriverInterface;
use Hyperf\AsyncQueue\Environment;
use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\Ast;
use Hyperf\Di\ReflectionManager;
use HyperfTest\AsyncQueue\Stub\FooProxy;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class AsyncQueueAspectTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        Context::set(FooProxy::class, null);
        ReflectionManager::clear();
    }

    /**
     * @group NonCoroutine
     */
    public function testNotAsyncMessage()
    {
        $container = $this->getContainer();
        $proxy = $container->get(FooProxy::class);

        $proxy->dump($id = rand(10000, 99999), $uuid = uniqid(), $data = [
            'id' => rand(0, 9999),
        ]);

        $this->assertSame([$id, $uuid, $data], Context::get(FooProxy::class));
    }

    /**
     * @group NonCoroutine
     */
    public function testAsyncMessage()
    {
        $container = $this->getContainer();
        $proxy = $container->get(FooProxy::class);

        $proxy->async($data = [
            'id' => rand(0, 9999),
        ]);

        $this->assertSame($data, Context::get(FooProxy::class));
    }

    /**
     * @group NonCoroutine
     */
    public function testAsyncMessageVariadic()
    {
        $container = $this->getContainer();
        $proxy = $container->get(FooProxy::class);

        $proxy->variadic($id = rand(10000, 99999), $uuid = uniqid(), $data = [
            'id' => rand(0, 9999),
        ]);

        $this->assertSame([$id, $uuid, $data], Context::get(FooProxy::class));
    }

    protected function getContainer()
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        $aspect = new Aspect();
        $aspect->collectClass(AsyncQueueAspect::class);
        AnnotationCollector::collectMethod(FooProxy::class, 'async', AsyncQueueMessage::class, new AsyncQueueMessage());
        AnnotationCollector::collectMethod(FooProxy::class, 'variadic', AsyncQueueMessage::class, new AsyncQueueMessage());

        $pid = pcntl_fork();
        if (! $pid) {
            $ast = new Ast();
            $code = $ast->proxy(FooProxy::class);
            if (! is_dir($dir = BASE_PATH . '/runtime/container/proxy/')) {
                mkdir($dir, 0777, true);
            }
            file_put_contents($file = $dir . 'FooProxy.proxy.php', $code);
            exit;
        }

        pcntl_wait($status);

        require_once BASE_PATH . '/runtime/container/proxy/FooProxy.proxy.php';

        $container->shouldReceive('get')->with(FooProxy::class)->andReturn(new FooProxy());
        $container->shouldReceive('get')->with(AsyncQueueAspect::class)->andReturnUsing(function ($_) use ($container) {
            return new AsyncQueueAspect($container);
        });
        $container->shouldReceive('get')->with(Environment::class)->andReturn($environment = new Environment());
        $container->shouldReceive('get')->with(DriverFactory::class)->andReturnUsing(function ($_) use ($environment) {
            $factory = Mockery::mock(DriverFactory::class);
            $driver = Mockery::mock(DriverInterface::class);
            $driver->shouldReceive('push')->andReturnUsing(function ($job) use ($environment) {
                $this->assertInstanceOf(AnnotationJob::class, $job);
                $environment->setAsyncQueue(true);
                $origin = new FooProxy();
                $origin->{$job->method}(...$job->params);
                return true;
            });
            $factory->shouldReceive('get')->andReturn($driver);
            return $factory;
        });
        return $container;
    }
}
