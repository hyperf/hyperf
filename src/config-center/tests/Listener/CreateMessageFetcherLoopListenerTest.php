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

namespace HyperfTest\ConfigCenter\Listener;

use Hyperf\Config\Config;
use Hyperf\ConfigCenter\Contract\DriverInterface;
use Hyperf\ConfigCenter\DriverFactory;
use Hyperf\ConfigCenter\Listener\CreateMessageFetcherLoopListener;
use Hyperf\ConfigCenter\Mode;
use Hyperf\Contract\StdoutLoggerInterface;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class CreateMessageFetcherLoopListenerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testRunningMode()
    {
        $config = new Config([
            'config_center' => [
                'enable' => true,
                'driver' => 'test',
                'mode' => Mode::COROUTINE,
            ],
        ]);

        $factory = Mockery::mock(DriverFactory::class);
        $driver = Mockery::mock(DriverInterface::class);
        $driver->shouldReceive('createMessageFetcherLoop')->once()->andReturnNull();
        $factory->shouldReceive('create')->with('test')->once()->andReturn($driver);

        $listener = new CreateMessageFetcherLoopListener($factory, $config, Mockery::mock(StdoutLoggerInterface::class));
        $listener->process(new stdClass());

        $config = new Config([
            'config_center' => [
                'mode' => Mode::PROCESS,
            ],
        ]);
        $listener = new CreateMessageFetcherLoopListener($factory, $config, Mockery::mock(StdoutLoggerInterface::class));
        $listener->process(new stdClass());
        $this->assertTrue(true);
    }
}
