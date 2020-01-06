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

namespace HyperfTest\JsonRpc;

use Hyperf\JsonRpc\JsonRpcPoolTransporter;
use Hyperf\JsonRpc\Pool\PoolFactory;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class JsonRpcPoolTransporterTest extends TestCase
{
    protected function tearDown()
    {
        Mockery::close();
    }

    public function testJsonRpcPoolTransporterConfig()
    {
        $factory = Mockery::mock(PoolFactory::class);
        $transporter = new JsonRpcPoolTransporter($factory, ['pool' => ['min_connections' => 10]]);

        $this->assertSame(10, $transporter->getConfig()['pool']['min_connections']);
        $this->assertSame(32, $transporter->getConfig()['pool']['max_connections']);
    }
}
