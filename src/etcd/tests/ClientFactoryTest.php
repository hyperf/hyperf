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

namespace HyperfTest\Etcd;

use Hyperf\Config\Config;
use Hyperf\Etcd\ClientFactory;
use Hyperf\Etcd\KVInterface;
use Hyperf\Etcd\V3\KV;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ClientFactoryTest extends TestCase
{
    public function testGetKyFromFactory()
    {
        $config = new Config([
            'uri' => 'http://127.0.0.1:2379',
            'version' => 'v3beta',
            'options' => [
                'timeout' => 10,
            ],
        ]);

        $factory = new ClientFactory($config);

        $kv = $factory->kv;

        $this->assertInstanceOf(KVInterface::class, $kv);
        $this->assertInstanceOf(KV::class, $kv);
    }
}
