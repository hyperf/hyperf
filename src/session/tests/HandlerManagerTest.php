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

namespace HyperfTest\Session;

use Hyperf\Session\Handler\FileHandler;
use Hyperf\Session\Handler\HandlerManager;
use Hyperf\Session\Handler\RedisHandler;
use HyperfTest\Session\Stub\FooHandler;
use HyperfTest\Session\Stub\NonSessionHandler;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @covers \Hyperf\Session\Handler\HandlerManager
 */
class HandlerManagerTest extends TestCase
{
    public function testGetHandler()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('has')->andReturnTrue();
        $container->shouldReceive('get')->with(FileHandler::class)->andReturn(Mockery::mock(FileHandler::class));
        $container->shouldReceive('get')->with(RedisHandler::class)->andReturn(Mockery::mock(RedisHandler::class));
        $handlerManager = new HandlerManager($container);
        $fileHandler = $handlerManager->getHandler('file');
        $this->assertInstanceOf(FileHandler::class, $fileHandler);
        $redisHandler = $handlerManager->getHandler('redis');
        $this->assertInstanceOf(RedisHandler::class, $redisHandler);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetHandlerUnRegister()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $handlerManager = new HandlerManager($container);
        $handlerManager->getHandler('un-register');
    }

    public function testRegister()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('has')->with(FooHandler::class)->andReturnTrue();
        $container->shouldReceive('get')->with(FooHandler::class)->andReturn(Mockery::mock(FooHandler::class));
        $handlerManager = new HandlerManager($container);
        $handlerManager->register('foo', FooHandler::class);
        $this->assertInstanceOf(FooHandler::class, $handlerManager->getHandler('foo'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRegisterNonSessionHandler()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('has')->with(NonSessionHandler::class)->andReturnTrue();
        $container->shouldReceive('get')->with(NonSessionHandler::class)->andReturn(Mockery::mock(NonSessionHandler::class));
        $handlerManager = new HandlerManager($container);
        $handlerManager->register('foo', NonSessionHandler::class);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetHandlerRegisteredButDoesNotExistInContainer()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('has')->andReturnFalse();
        $handlerManager = new HandlerManager($container);
        $name = 'does-not-exist-in-container';
        $handlerManager->register($name, 'Not-Exist-Classs');
        $handlerManager->getHandler('does-not-exist-in-container');
    }
}
