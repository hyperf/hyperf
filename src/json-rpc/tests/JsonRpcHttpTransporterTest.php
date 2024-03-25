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

namespace HyperfTest\JsonRpc;

use GuzzleHttp\Client;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\JsonRpc\JsonRpcHttpTransporter;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class JsonRpcHttpTransporterTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testJsonRpcHttpTransporterConfig()
    {
        $factory = Mockery::mock(ClientFactory::class);
        $factory->shouldReceive('create')->once()->with(['timeout' => 3])->andReturn(new Client());
        $transporter = new JsonRpcHttpTransporter($factory, [
            'connect_timeout' => 1,
            'recv_timeout' => 2,
        ]);
        $transporter->getClient();

        $this->assertSame(1, $transporter->getClientOptions()['connect_timeout']);
        $this->assertSame(2, $transporter->getClientOptions()['recv_timeout']);
    }
}
