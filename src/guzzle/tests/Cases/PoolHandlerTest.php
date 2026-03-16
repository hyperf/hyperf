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
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Container;
use Hyperf\Pool\Channel;
use Hyperf\Pool\PoolOption;
use Hyperf\Pool\SimplePool\Connection;
use Hyperf\Pool\SimplePool\Pool;
use Hyperf\Pool\SimplePool\PoolFactory;
use HyperfTest\Guzzle\Stub\PoolHandlerStub;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class PoolHandlerTest extends TestCase
{
    protected $id = 0;

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testTryFinally()
    {
        $this->get();

        $this->assertSame(2, $this->id);
    }

    public function testPoolHandler()
    {
        $container = $this->getContainer();
        $client = new Client([
            'handler' => $handler = new PoolHandlerStub($container->get(PoolFactory::class), []),
            'base_uri' => 'http://127.0.0.1:4151',
        ]);

        $res = $client->get('/stats?format=json');
        $this->assertSame(200, $res->getStatusCode());
        $this->assertIsArray(json_decode((string) $res->getBody(), true));
        $this->assertSame(1, $handler->count);
        $client->get('/stats?format=json');
        $this->assertSame(1, $handler->count);
    }

    protected function get()
    {
        try {
            $this->id = 1;
            return;
        } finally {
            $this->id = 2;
        }
    }

    protected function getContainer()
    {
        $container = Mockery::mock(Container::class);
        $container->shouldReceive('make')->with(PoolOption::class, Mockery::andAnyOtherArgs())->andReturnUsing(function ($_, $args) {
            return new PoolOption(...array_values($args));
        });
        $container->shouldReceive('make')->with(Pool::class, Mockery::andAnyOtherArgs())->andReturnUsing(function ($_, $args) use ($container) {
            return new Pool($container, $args['callback'], $args['option']);
        });
        $container->shouldReceive('get')->with(PoolFactory::class)->andReturnUsing(function () use ($container) {
            return new PoolFactory($container);
        });
        $container->shouldReceive('make')->with(Channel::class, Mockery::any())->andReturnUsing(function ($_, $args) {
            return new Channel($args['size']);
        });
        $container->shouldReceive('make')->with(Connection::class, Mockery::any())->andReturnUsing(function ($_, $args) use ($container) {
            return new Connection($container, $args['pool'], $args['callback']);
        });
        $container->shouldReceive('has')->with(StdoutLoggerInterface::class)->andReturnFalse();
        $container->shouldReceive('has')->with(EventDispatcherInterface::class)->andReturnFalse();

        ApplicationContext::setContainer($container);
        return $container;
    }
}
