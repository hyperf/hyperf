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
namespace HyperfTest\Etcd;

use GuzzleHttp\Client;
use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Container;
use Hyperf\Etcd\KVFactory;
use Hyperf\Etcd\KVInterface;
use Hyperf\Etcd\V3\EtcdClient;
use Hyperf\Etcd\V3\KV;
use Hyperf\Guzzle\HandlerStackFactory;
use Hyperf\Guzzle\PoolHandler;
use Hyperf\Pool\Channel;
use Hyperf\Pool\PoolOption;
use Hyperf\Pool\SimplePool\Connection;
use Hyperf\Pool\SimplePool\Pool;
use Hyperf\Pool\SimplePool\PoolFactory;
use HyperfTest\Etcd\Stub\GuzzleClientStub;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class KVTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testGetKyFromFactory()
    {
        $kv = $this->getKVClient();

        $this->assertInstanceOf(KVInterface::class, $kv);
    }

    public function testPutAndGet()
    {
        $kv = $this->getKVClient();

        $res = $kv->put('/test/test2', 'Hello World!');

        $this->assertArrayHasKey('header', $res);

        $res = $kv->get('/test/test2');

        $this->assertSame('Hello World!', $res['kvs'][0]['value']);
    }

    /**
     * @return KVInterface
     */
    protected function getKVClient()
    {
        $config = new Config([
            'etcd' => [
                'uri' => 'http://127.0.0.1:2379',
                'version' => 'v3beta',
                'options' => [
                    'timeout' => 10,
                ],
            ],
        ]);

        $container = Mockery::mock(Container::class);
        ApplicationContext::setContainer($container);

        $container->shouldReceive('make')->with(EtcdClient::class, Mockery::any())->andReturnUsing(function ($class, $args) {
            return new EtcdClient($args['client']);
        });
        $container->shouldReceive('make')->with(Client::class, Mockery::any())->andReturnUsing(function ($class, $args) {
            return new GuzzleClientStub($args['config']);
        });
        $container->shouldReceive('make')->with(PoolHandler::class, Mockery::any())->andReturnUsing(function ($class, $args) use ($container) {
            return new PoolHandler(new PoolFactory($container), $args['option']);
        });
        $container->shouldReceive('make')->with(Channel::class, Mockery::any())->andReturnUsing(function ($class, $args) {
            return new Channel($args['size']);
        });
        $container->shouldReceive('make')->with(Connection::class, Mockery::any())->andReturnUsing(function ($class, $args) use ($container) {
            return new Connection($container, $args['pool'], $args['callback']);
        });
        $container->shouldReceive('make')->with(PoolOption::class, Mockery::any())->andReturnUsing(function ($class, $args) {
            return new PoolOption(...array_values($args));
        });
        $container->shouldReceive('make')->with(Pool::class, Mockery::any())->andReturnUsing(function ($class, $args) use ($container) {
            return new Pool($container, $args['callback'], $args['option']);
        });
        $container->shouldReceive('make')->with(KV::class, Mockery::any())->andReturnUsing(function ($class, $args) {
            return new KV(...array_values($args));
        });
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn($config);
        $container->shouldReceive('get')->with(HandlerStackFactory::class)->andReturn(new HandlerStackFactory());

        $factory = new KVFactory();
        return $factory($container);
    }
}
