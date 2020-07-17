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

use Hyperf\Guzzle\ClientFactory;
use Hyperf\JsonRpc\JsonRpcHttpTransporter;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class JsonRpcHttpTransporterTest extends TestCase
{
    protected function tearDown()
    {
        Mockery::close();
    }

    public function testJsonRpcHttpTransporterConfig()
    {
        $factory = Mockery::mock(ClientFactory::class);
        $transporter = new JsonRpcHttpTransporter($factory, [
            'connect_timeout' => 1,
            'recv_timeout' => 2,
        ]);

        $this->assertSame(1, $transporter->getConfig()['connect_timeout']);
        $this->assertSame(2, $transporter->getConfig()['recv_timeout']);
    }
}
