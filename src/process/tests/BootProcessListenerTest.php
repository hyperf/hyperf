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

namespace HyperfTest\Process;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Process\Annotation\Process;
use Hyperf\Process\Listener\BootProcessListener;
use HyperfTest\Process\Stub\FooProcess;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionClass;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
/**
 * @internal
 * @coversNothing
 */
class BootProcessListenerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        AnnotationCollector::clear();
    }

    public function testGetAnnotationProcesses()
    {
        $annotation = new Process(name: 'foo');
        $annotation->collectClass(FooProcess::class);
        $listener = new BootProcessListener(Mockery::mock(ContainerInterface::class), Mockery::mock(ConfigInterface::class));
        $ref = new ReflectionClass($listener);
        $method = $ref->getMethod('getAnnotationProcesses');
        $res = $method->invoke($listener);
        foreach ($res as $class => $annotation) {
            $this->assertSame(FooProcess::class, $class);
            $this->assertInstanceOf(Process::class, $annotation);
        }
    }
}
