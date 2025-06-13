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
use Hyperf\Engine\Coroutine;
use Hyperf\Watcher\Driver\FswatchDriver;
use Hyperf\Watcher\Option;
use HyperfTest\Watcher\Stub\ContainerStub;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

use function Hyperf\Watcher\exec;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class FswatchDriverTest extends TestCase
{
    public function testWatch()
    {
        $container = ContainerStub::getContainer(FswatchDriver::class);
        $option = new Option($container->get(ConfigInterface::class)->get('watcher'), [], []);
        $channel = new Channel(10);
        try {
            $driver = new FswatchDriver($option);
            Coroutine::create(fn () => $driver->watch($channel));
            exec('echo 1 > /tmp/.env');
            $this->assertStringEndsWith('.env', $channel->pop($option->getScanIntervalSeconds() + 0.1));
        } catch (InvalidArgumentException $e) {
            if (str_contains($e->getMessage(), 'fswatch not exists')) {
                $this->markTestSkipped();
            }
            throw $e;
        } finally {
            if (! empty($driver)) {
                // Need to close the fswatch child process manually.
                $driver->stop();
            }
            $channel->close();
        }
    }
}
