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
    public function testUdpSocketHook()
    {
        $socket = Mockery::mock(\Swoole\Coroutine\Socket::class);

        $this->assertTrue(\Monolog\Handler\SyslogUdp\is_resource($socket));
    }
}
