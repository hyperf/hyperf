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
use Hyperf\Engine\Channel;
use Hyperf\Watcher\Driver\FindNewerDriver;
use Hyperf\Watcher\Option;
use HyperfTest\Watcher\Stub\ContainerStub;
use HyperfTest\Watcher\Stub\FindNewerDriverStub;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class FindNewerDriverTest extends TestCase
{
    public function testWatch()
    {
        $container = ContainerStub::getContainer(FindNewerDriver::class);
        $option = new Option($container->get(ConfigInterface::class)->get('watcher'), [], []);
        $channel = new Channel(10);

        try {
            $driver = new FindNewerDriverStub($option);
            $driver->watch($channel);
            $this->assertSame('.env', $channel->pop($option->getScanIntervalSeconds() + 0.1));
        } catch (InvalidArgumentException $e) {
            if (str_contains($e->getMessage(), 'find not exists')) {
                $this->markTestSkipped();
            }
            throw $e;
        } finally {
            $channel->close();
        }
    }
}
