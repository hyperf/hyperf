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
namespace HyperfTest\Nsq;

use Hyperf\Codec\Json;
use Hyperf\Engine\Contract\SocketInterface;
use Hyperf\Nsq\Subscriber;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class SubscriberTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testSubscriberRecv()
    {
        $socket = Mockery::mock(SocketInterface::class);
        $data = base64_decode('eyJtYXhfcmR5X2NvdW50IjoyNTAwLCJ2ZXJzaW9uIjoiMS4yLjAiLCJtYXhfbXNnX3RpbWVvdXQiOjkwMDAwMCwibXNnX3RpbWVvdXQiOjYwMDAwLCJ0bHNfdjEiOmZhbHNlLCJkZWZsYXRlIjpmYWxzZSwiZGVmbGF0ZV9sZXZlbCI6NiwibWF4X2RlZmxhdGVfbGV2ZWwiOjYsInNuYXBweSI6ZmFsc2UsInNhbXBsZV9yYXRlIjowLCJhdXRoX3JlcXVpcmVkIjp0cnVlLCJvdXRwdXRfYnVmZmVyX3NpemUiOjE2Mzg0LCJvdXRwdXRfYnVmZmVyX3RpbWVvdXQiOjI1MH0=');
        $socket->shouldReceive('recvAll')->with(8)->andReturn(base64_decode('AAABCwAAAAA='));
        $socket->shouldReceive('recvAll')->with(263)->andReturn(substr($data, 0, 260));
        $socket->shouldReceive('recvAll')->with(3)->andReturn(substr($data, 260, 3));

        $reader = new Subscriber($socket);
        $reader->recv();

        $this->assertIsArray(Json::decode($reader->getPayload()));
    }
}
