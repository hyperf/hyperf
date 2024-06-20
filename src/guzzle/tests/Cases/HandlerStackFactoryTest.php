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

namespace HyperfTest\Guzzle\Cases;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\HandlerStack;
use Hyperf\Context\ApplicationContext;
use Hyperf\Di\Container;
use Hyperf\Guzzle\CoroutineHandler;
use Hyperf\Guzzle\HandlerStackFactory;
use Hyperf\Guzzle\PoolHandler;
use Hyperf\Guzzle\RetryMiddleware;
use Hyperf\Pool\SimplePool\PoolFactory;
use HyperfTest\Guzzle\Stub\CoroutineHandlerStub;
use HyperfTest\Guzzle\Stub\HandlerStackFactoryStub;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use Throwable;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
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

        $ref = new ReflectionClass($stack);

        $handler = $ref->getProperty('handler');
        $this->assertInstanceOf(CoroutineHandler::class, $handler->getValue($stack));

        $property = $ref->getProperty('stack');
        foreach ($property->getValue($stack) as $stack) {
            $this->assertTrue(in_array($stack[1], ['http_errors', 'allow_redirects', 'cookies', 'prepare_body', 'retry']));
        }
    }

    public function testMakeCoroutineHandler()
    {
        $container = Mockery::mock(Container::class);
        ApplicationContext::setContainer($container);
        $container->shouldReceive('make')->with(CoroutineHandler::class, Mockery::any())->andReturn(new CoroutineHandler());

        $factory = new HandlerStackFactoryStub();
        $stack = $factory->create();
        $this->assertInstanceOf(HandlerStack::class, $stack);
        $this->assertTrue($stack->hasHandler());

        $ref = new ReflectionClass($stack);

        $handler = $ref->getProperty('handler');
        $this->assertInstanceOf(CoroutineHandler::class, $handler->getValue($stack));

        $property = $ref->getProperty('stack');
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

        $ref = new ReflectionClass($stack);

        $handler = $ref->getProperty('handler');
        $this->assertInstanceOf(PoolHandler::class, $handler->getValue($stack));

        $property = $ref->getProperty('stack');
        $items = array_column($property->getValue($stack), 1);

        $this->assertEquals(['http_errors', 'allow_redirects', 'cookies', 'prepare_body', 'retry'], $items);
    }

    public function testPoolHandlerOption()
    {
        $this->setContainer();

        $factory = new HandlerStackFactory();
        $stack = $factory->create(['max_connections' => 50]);

        $ref = new ReflectionClass($stack);
        $handler = $ref->getProperty('handler');
        $handler = $handler->getValue($stack);

        $ref = new ReflectionClass($handler);
        $option = $ref->getProperty('option');

        $this->assertSame(50, $option->getValue($handler)['max_connections']);
    }

    public function testPoolHandlerMiddleware()
    {
        $this->setContainer();

        $factory = new HandlerStackFactory();
        $stack = $factory->create([], ['retry_again' => [RetryMiddleware::class, [1, 10]]]);

        $ref = new ReflectionClass($stack);
        $property = $ref->getProperty('stack');
        $items = array_column($property->getValue($stack), 1);
        $this->assertEquals(['http_errors', 'allow_redirects', 'cookies', 'prepare_body', 'retry', 'retry_again'], $items);
    }

    public function testRetryMiddleware()
    {
        $this->setContainer();

        $factory = new HandlerStackFactory();
        $stack = $factory->create([], ['retry_again' => [RetryMiddleware::class, [1, 10]]]);
        $stack->setHandler($stub = new CoroutineHandlerStub(201));

        $client = new Client([
            'handler' => $stack,
            'base_uri' => 'http://127.0.0.1:9501',
        ]);

        $resposne = $client->get('/');
        $this->assertSame(201, $resposne->getStatusCode());
        $this->assertSame(1, $stub->count);

        $stack = $factory->create([], ['retry' => [RetryMiddleware::class, [1, 10]]]);
        $stack->setHandler($stub = new CoroutineHandlerStub(400));
        $client = new Client([
            'handler' => $stack,
            'base_uri' => 'http://127.0.0.1:9501',
        ]);

        $this->expectExceptionCode(400);
        $this->expectException(ClientException::class);
        $this->expectExceptionMessageMatches('/400 Bad Request/');

        try {
            $client->get('/');
        } catch (Throwable $exception) {
            $this->assertSame(2, $stub->count);
            throw $exception;
        }
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
