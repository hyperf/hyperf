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
namespace HyperfTest\RpcServer;

use Hyperf\Rpc\Contract\PathGeneratorInterface;
use Hyperf\Rpc\PathGenerator\PathGenerator;
use Hyperf\RpcServer\Annotation\RpcService;
use Hyperf\RpcServer\Event\AfterPathRegister;
use Hyperf\RpcServer\Router\DispatcherFactory;
use HyperfTest\RpcServer\Stub\ContainerStub;
use HyperfTest\RpcServer\Stub\IdGeneratorStub;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 * @coversNothing
 */
class RouterDispatcherFactoryTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testHandleRpcService()
    {
        $container = ContainerStub::getContainer();
        $container->shouldReceive('get')->with(EventDispatcherInterface::class)->andReturnUsing(function () {
            $dispatcher = Mockery::mock(EventDispatcherInterface::class);
            $dispatcher->shouldReceive('dispatch')->withAnyArgs()->once()->andReturnUsing(function ($object) {
                $this->assertInstanceOf(AfterPathRegister::class, $object);
                $this->assertSame('/id_generator_stub/generate', $object->path);
                return $object;
            });
            return $dispatcher;
        });
        $container->shouldReceive('get')->with(PathGeneratorInterface::class)->andReturn(new PathGenerator());
        $factory = new DispatcherFactory(
            $container->get(EventDispatcherInterface::class),
            $container->get(PathGeneratorInterface::class)
        );
        $ref = new \ReflectionClass($factory);
        $m = $ref->getMethod('handleRpcService');
        $m->setAccessible(true);
        $m->invokeArgs($factory, [IdGeneratorStub::class, new RpcService('IdGenerator'), [], []]);
    }
}
