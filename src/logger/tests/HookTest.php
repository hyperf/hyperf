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

use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class HookTest extends TestCase
{
    public function testUdpSocketHook()
    {
        run(function () {
            $socket = socket_create(AF_UNIX, SOCK_DGRAM, 0);

            $this->assertTrue(\Monolog\Handler\SyslogUdp\is_resource($socket));
        });
    }
}
