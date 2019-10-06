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

namespace HyperfTest\Etcd;

use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Etcd\KVFactory;
use Hyperf\Etcd\KVInterface;
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

        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn($config);

        $factory = new KVFactory();
        $kv = $factory($container);

        $this->assertInstanceOf(KVInterface::class, $kv);
    }
}
