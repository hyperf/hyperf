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
use ReflectionMethod;
use Swoole\Coroutine;
use Swoole\Coroutine\Http\Server;
use Throwable;

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

    public function testPoolHandlerReusesConnectionAfterStreamedBody()
    {
        $handler = new PoolHandlerStub($this->getContainer()->get(PoolFactory::class), []);
        if (! (new ReflectionMethod($handler, 'supportsStreamBody'))->invoke($handler)) {
            $this->markTestSkipped('The installed Swoole version does not support streaming request bodies.');
        }

        $port = 19512;
        $received = [];
        $server = new Server('127.0.0.1', $port, false);
        $server->set(['package_max_length' => 8 * 1024 * 1024]);
        Coroutine::create(function () use ($server, &$received) {
            $server->handle('/upload', function ($request, $response) use ($server, &$received) {
                $received[] = [
                    'body' => $request->rawContent(),
                    'content_length' => $request->header['content-length'] ?? null,
                    'transfer_encoding' => $request->header['transfer-encoding'] ?? null,
                ];
                $response->end('ok');
                if (count($received) >= 2) {
                    Coroutine::create(function () use ($server) {
                        Coroutine::sleep(0.01);
                        $server->shutdown();
                    });
                }
            });
            $server->start();
        });
        Coroutine::sleep(0.1);

        $dir = BASE_PATH . '/runtime/guzzle/';
        @mkdir($dir, 0755, true);
        $path = $dir . uniqid('pool_stream_', true);
        $content = str_repeat('a', 1024 * 1024 + 4096);
        file_put_contents($path, $content);

        try {
            $client = new Client([
                'handler' => $handler,
                'base_uri' => "http://127.0.0.1:{$port}",
            ]);

            $this->assertSame(200, $client->put('/upload', ['body' => fopen($path, 'rb')])->getStatusCode());
            $this->assertSame(200, $client->post('/upload', ['body' => 'small'])->getStatusCode());

            $this->assertSame(1, $handler->count);
            $this->assertCount(2, $received);
            $this->assertSame($content, $received[0]['body']);
            $this->assertSame((string) strlen($content), (string) $received[0]['content_length']);
            $this->assertNull($received[0]['transfer_encoding']);
            $this->assertSame('small', $received[1]['body']);
        } finally {
            @unlink($path);
            try {
                $server->shutdown();
            } catch (Throwable) {
            }
        }
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
