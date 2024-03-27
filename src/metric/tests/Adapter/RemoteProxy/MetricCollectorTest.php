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

namespace HyperfTest\Metric\Adapter\RemoteProxy;

use Hyperf\Metric\Adapter\RemoteProxy\MetricCollector;
use Hyperf\Process\ProcessCollector;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use stdClass;
use Swoole\Process;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class MetricCollectorTest extends TestCase
{
    protected bool $processMocked = false;

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testAddData()
    {
        $collector = new MetricCollector(100);

        $this->assertSame(0, count($collector->getBuffer()));

        $collector->add($data = new stdClass());

        $this->assertSame(1, count($collector->getBuffer()));
    }

    public function testFlush()
    {
        $this->mockProcessCollector();

        $collector = new MetricCollector(100);
        $collector->add(new stdClass());
        $collector->flush();

        $this->assertSame(0, count($collector->getBuffer()));
    }

    public function testAddDataWithFlush()
    {
        $this->mockProcessCollector();

        $collector = new MetricCollector(1);
        $collector->add(new stdClass());

        $this->assertSame(0, count($collector->getBuffer()));
    }

    protected function mockProcessCollector()
    {
        if ($this->processMocked) {
            return;
        }

        $process = Mockery::mock(Process::class);
        $process->shouldReceive('write');

        ProcessCollector::add('metric', $process);

        $this->processMocked = true;
    }
}
