<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Etcd;

use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Container;
use Hyperf\Etcd\KVFactory;
use Hyperf\Etcd\KVInterface;
use Hyperf\Etcd\V3\KV;
use Hyperf\Guzzle\HandlerStackFactory;
use Hyperf\Guzzle\PoolHandler;
use Hyperf\Utils\ApplicationContext;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class KVTest extends TestCase
{
    protected function tearDown()
    {
        Mockery::close();
    }

    public function testGetKyFromFactory()
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

        $container->shouldReceive('make')->with(PoolHandler::class, Mockery::any())->andReturnUsing(function ($class, $args) {
            var_dump($class, $args);
        });
        $container->shouldReceive('make')->with(KV::class, Mockery::any())->andReturnUsing(function ($class, $args) {
            return new KV(...array_values($args));
        });
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn($config);
        $container->shouldReceive('get')->with(HandlerStackFactory::class)->andReturn(new HandlerStackFactory());

        $factory = new KVFactory();
        $kv = $factory($container);

        $this->assertInstanceOf(KVInterface::class, $kv);
    }
}
