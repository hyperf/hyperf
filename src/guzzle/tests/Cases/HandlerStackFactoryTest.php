<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE.md
 */

namespace HyperfTest\Guzzle\Cases;

use GuzzleHttp\HandlerStack;
use Hyperf\Di\Container;
use Hyperf\Guzzle\CoroutineHandler;
use Hyperf\Guzzle\HandlerStackFactory;
use Hyperf\Guzzle\PoolHandler;
use Hyperf\Guzzle\RetryMiddleware;
use Hyperf\Pool\SimplePool\PoolFactory;
use Hyperf\Utils\ApplicationContext;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class HandlerStackFactoryTest extends TestCase
{
    public function testCreateCoroutineHandler()
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        $factory = new HandlerStackFactory();
        $stack = $factory->create();
        $this->assertInstanceOf(HandlerStack::class, $stack);
        $this->assertTrue($stack->hasHandler());

        $ref = new \ReflectionClass($stack);

        $handler = $ref->getProperty('handler');
        $handler->setAccessible(true);
        $this->assertInstanceOf(CoroutineHandler::class, $handler->getValue($stack));

        $property = $ref->getProperty('stack');
        $property->setAccessible(true);
        foreach ($property->getValue($stack) as $stack) {
            $this->assertTrue(in_array($stack[1], ['http_errors', 'allow_redirects', 'cookies', 'prepare_body', 'retry']));
        }
    }

    public function testCreatePoolHandler()
    {
        $this->setContainer();

        $factory = new HandlerStackFactory();
        $stack = $factory->create();
        $this->assertTrue($stack->hasHandler());
        $this->assertInstanceOf(HandlerStack::class, $stack);

        $ref = new \ReflectionClass($stack);

        $handler = $ref->getProperty('handler');
        $handler->setAccessible(true);
        $this->assertInstanceOf(PoolHandler::class, $handler->getValue($stack));

        $property = $ref->getProperty('stack');
        $property->setAccessible(true);
        $items = array_column($property->getValue($stack), 1);

        $this->assertEquals(['http_errors', 'allow_redirects', 'cookies', 'prepare_body', 'retry'], $items);
    }

    public function testPoolHandlerOption()
    {
        $this->setContainer();

        $factory = new HandlerStackFactory();
        $stack = $factory->create(['max_connections' => 50]);

        $ref = new \ReflectionClass($stack);
        $handler = $ref->getProperty('handler');
        $handler->setAccessible(true);
        $handler = $handler->getValue($stack);

        $ref = new \ReflectionClass($handler);
        $option = $ref->getProperty('option');
        $option->setAccessible(true);

        $this->assertSame(50, $option->getValue($handler)['max_connections']);
    }

    public function testPoolHandlerMiddleware()
    {
        $this->setContainer();

        $factory = new HandlerStackFactory();
        $stack = $factory->create([], ['retry_again' => [RetryMiddleware::class, [1, 10]]]);

        $ref = new \ReflectionClass($stack);
        $property = $ref->getProperty('stack');
        $property->setAccessible(true);
        $items = array_column($property->getValue($stack), 1);
        $this->assertEquals(['http_errors', 'allow_redirects', 'cookies', 'prepare_body', 'retry', 'retry_again'], $items);
    }

    protected function setContainer()
    {
        $container = Mockery::mock(Container::class);
        $factory = new PoolFactory($container);
        $container->shouldReceive('make')->with(PoolHandler::class, Mockery::any())->andReturnUsing(function ($class, $args) use ($factory) {
            return new PoolHandler($factory, $args['option']);
        });

        ApplicationContext::setContainer($container);
    }
}
