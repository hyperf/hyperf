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

namespace HyperfTest\Watcher\Driver;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Engine\Channel;
use Hyperf\Watcher\Driver\ScanFileDriver;
use Hyperf\Watcher\Option;
use HyperfTest\Watcher\Stub\ContainerStub;
use HyperfTest\Watcher\Stub\ScanFileDriverStub;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

use function Hyperf\Watcher\exec;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ScanFileDriverTest extends TestCase
{
    public function testWatch()
    {
        $container = ContainerStub::getContainer(ScanFileDriver::class);
        $option = new Option($container->get(ConfigInterface::class)->get('watcher'), [], []);

        $channel = new Channel(10);
        $driver = new ScanFileDriverStub($option, $container->get(StdoutLoggerInterface::class));

        $driver->watch($channel);

        exec('echo 1 > /tmp/.env');
        $this->assertStringEndsWith('.env', $channel->pop($option->getScanIntervalSeconds() + 0.1));
        $channel->close();
    }
}
