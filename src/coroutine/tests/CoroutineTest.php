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
namespace HyperfTest\Coroutine;

use Exception;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Engine\Channel;
use Hyperf\Engine\Exception\CoroutineDestroyedException;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Throwable;

use function Hyperf\Coroutine\defer;
use function Hyperf\Coroutine\go;
use function Hyperf\Coroutine\run;

/**
 * @internal
 * @coversNothing
 */
class CoroutineTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCoroutineParentId()
    {
        $pid = Coroutine::id();
        Coroutine::create(function () use ($pid) {
            $this->assertSame($pid, Coroutine::parentId());
            $pid = Coroutine::id();
            $id = Coroutine::create(function () use ($pid) {
                $this->assertSame($pid, Coroutine::parentId(Coroutine::id()));
                usleep(1000);
            });
            Coroutine::create(function () use ($pid) {
                $this->assertSame($pid, Coroutine::parentId());
            });
            $this->assertSame($pid, Coroutine::parentId($id));
        });
    }

    public function testCoroutineParentIdHasBeenDestroyed()
    {
        $id = Coroutine::create(function () {
        });

        try {
            Coroutine::parentId($id);
            $this->assertTrue(false);
        } catch (Throwable $exception) {
            $this->assertInstanceOf(CoroutineDestroyedException::class, $exception);
        }
    }

    /**
     * @group NonCoroutine
     */
    public function testCoroutineInTopCoroutine()
    {
        run(function () {
            $this->assertSame(0, Coroutine::parentId());
        });
    }

    public function testCoroutineAndDeferWithException()
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        $container->shouldReceive('has')->withAnyArgs()->andReturnTrue();
        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturn($logger = Mockery::mock(StdoutLoggerInterface::class));
        $logger->shouldReceive('warning')->with('unit')->twice()->andReturnNull();
        $container->shouldReceive('get')->with(FormatterInterface::class)->andReturn($formatter = Mockery::mock(FormatterInterface::class));
        $formatter->shouldReceive('format')->with($exception = new Exception())->twice()->andReturn('unit');

        $chan = new Channel(1);
        go(static function () use ($chan, $exception) {
            defer(static function () use ($chan, $exception) {
                try {
                    throw $exception;
                } finally {
                    $chan->push(1);
                }
            });

            throw $exception;
        });

        $this->assertTrue(true);
    }
}
