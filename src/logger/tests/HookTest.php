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
namespace HyperfTest\Logger;

use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class HookTest extends TestCase
{
    /**
     * @group NonCoroutine
     * @covers \Monolog\Handler\SyslogUdp\is_resource
     */
    public function testUdpSocketHook()
    {
        run(function () {
            // $socket = Mockery::mock(\Swoole\Coroutine\Socket::class);
            $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

            $this->assertTrue(\Monolog\Handler\SyslogUdp\is_resource($socket));
            $this->assertFalse(\is_resource($socket));

            socket_close($socket);
        });
    }
}
