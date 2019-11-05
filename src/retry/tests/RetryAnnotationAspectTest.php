<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Retry;

use Hyperf\Di\Aop\AnnotationMetadata;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Di\Container;
use Hyperf\Retry\Annotation\Retry;
use Hyperf\Retry\Aspect\RetryAnnotationAspect;
use Hyperf\Retry\BackoffStrategy;
use Hyperf\Retry\FlatStrategy;
use Hyperf\Retry\NoOpRetryBudget;
use Hyperf\Retry\RetryBudgetInterface;
use Hyperf\Utils\ApplicationContext;
use Mockery;
use PHPUnit\Framework\TestCase;
use Swoole\Timer;

/**
 * @internal
 * @coversNothing
 */
class RetryAnnotationAspectTest extends TestCase
{
    protected function setUp(): void
    {
        $this->mockContainer();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        Timer::clearAll();
    }

    public function testDefault()
    {
        $aspect = new RetryAnnotationAspect();
        $point = Mockery::mock(ProceedingJoinPoint::class);

        $point->shouldReceive('getAnnotationMetadata')->andReturns(
            new class() extends AnnotationMetadata {
                public $method;

                public function __construct()
                {
                    $retry = new Retry();
                    $retry->strategy = FlatStrategy::class;
                    $this->method = [
                        Retry::class => $retry,
                    ];
                }
            }
        );
        $point->shouldReceive('process')->once()->andThrow(new \RuntimeException());
        $point->shouldReceive('process')->once()->andThrows(new \Exception());
        $point->shouldReceive('process')->once()->andReturns(
            true
        );
        $this->assertTrue($aspect->process($point));
    }

    public function testMaximumAttempts()
    {
        $aspect = new RetryAnnotationAspect();
        $point = Mockery::mock(ProceedingJoinPoint::class);

        $point->shouldReceive('getAnnotationMetadata')->andReturns(
            new class() extends AnnotationMetadata {
                public $method;

                public function __construct()
                {
                    $retry = new Retry();
                    $retry->strategy = FlatStrategy::class;
                    $retry->maxAttempts = 2;
                    $this->method = [
                        Retry::class => $retry,
                    ];
                }
            }
        );
        $point->shouldReceive('process')->twice()->andThrowExceptions(
            [
                new \Exception(),
                new \Exception(),
            ]
        );
        $point->shouldReceive('process')->never()->andReturns(
            true
        );
        $this->expectException(\Exception::class);
        $aspect->process($point);
    }

    public function testIgnoreThrowables()
    {
        $aspect = new RetryAnnotationAspect();
        $point = Mockery::mock(ProceedingJoinPoint::class);

        $point->shouldReceive('getAnnotationMetadata')->andReturns(
            new class() extends AnnotationMetadata {
                public $method;

                public function __construct()
                {
                    $retry = new Retry();
                    $retry->strategy = FlatStrategy::class;
                    $retry->ignoreThrowables = [\RuntimeException::class];
                    $this->method = [
                        Retry::class => $retry,
                    ];
                }
            }
        );
        $point->shouldReceive('process')->twice()->andThrowExceptions(
            [
                new \Exception(),
                new \RuntimeException(),
            ]
        );
        $point->shouldReceive('process')->never()->andReturns(
            true
        );
        $this->expectException(\RuntimeException::class);
        $aspect->process($point);
    }

    public function testBackoff()
    {
        $aspect = new RetryAnnotationAspect();
        $point = Mockery::mock(ProceedingJoinPoint::class);

        $point->shouldReceive('getAnnotationMetadata')->andReturns(
            new class() extends AnnotationMetadata {
                public $method;

                public function __construct()
                {
                    $retry = new Retry();
                    $retry->strategy = BackoffStrategy::class;
                    $this->method = [
                        Retry::class => $retry,
                    ];
                }
            }
        );
        $point->shouldReceive('process')->once()->andThrows(new \RuntimeException());
        $point->shouldReceive('process')->once()->andThrows(new \Exception());
        $point->shouldReceive('process')->once()->andReturns(
            true
        );
        $this->assertTrue($aspect->process($point));
    }

    public function testRetryThrowables()
    {
        $aspect = new RetryAnnotationAspect();
        $point = Mockery::mock(ProceedingJoinPoint::class);

        $point->shouldReceive('getAnnotationMetadata')->andReturns(
            new class() extends AnnotationMetadata {
                public $method;

                public function __construct()
                {
                    $retry = new Retry();
                    $retry->strategy = BackoffStrategy::class;
                    $retry->retryThrowables = [\RuntimeException::class];
                    $this->method = [
                        Retry::class => $retry,
                    ];
                }
            }
        );
        $point->shouldReceive('process')->once()->andThrows(new \RuntimeException());
        $point->shouldReceive('process')->once()->andThrows(new \InvalidArgumentException());
        $point->shouldReceive('process')->never()->andReturns(
            true
        );
        $this->expectException(\InvalidArgumentException::class);
        $aspect->process($point);
    }

    public function testRetryOnThrowablePredicate()
    {
        $aspect = new RetryAnnotationAspect();
        $point = Mockery::mock(ProceedingJoinPoint::class);

        $point->shouldReceive('getAnnotationMetadata')->andReturns(
            new class() extends AnnotationMetadata {
                public $method;

                public function __construct()
                {
                    $retry = new Retry();
                    $retry->strategy = FlatStrategy::class;
                    $retry->retryOnThrowablePredicate = function ($t) {
                        return $t->getMessage() === 'ok';
                    };
                    $retry->retryThrowables = [];
                    $retry->maxAttempts = 5;
                    $this->method = [
                        Retry::class => $retry,
                    ];
                }
            }
        );
        $point->shouldReceive('process')->once()->andThrow(new \RuntimeException('ok'));
        $point->shouldReceive('process')->once()->andThrows(new \InvalidArgumentException('not ok'));
        $point->shouldReceive('process')->never()->andReturns(
            true
        );
        $this->expectException(\InvalidArgumentException::class);
        $this->assertTrue($aspect->process($point));
    }

    public function testRetryOnResultPredicate()
    {
        $aspect = new RetryAnnotationAspect();
        $point = Mockery::mock(ProceedingJoinPoint::class);

        $point->shouldReceive('getAnnotationMetadata')->andReturns(
            new class() extends AnnotationMetadata {
                public $method;

                public function __construct()
                {
                    $retry = new Retry();
                    $retry->strategy = FlatStrategy::class;
                    $retry->retryOnResultPredicate = function ($r) {
                        return $r <= 0;
                    };
                    $retry->retryThrowables = [];
                    $retry->maxAttempts = 5;
                    $this->method = [
                        Retry::class => $retry,
                    ];
                }
            }
        );
        $point->shouldReceive('process')->once()->andReturns(-1);
        $point->shouldReceive('process')->once()->andReturns(0);
        $point->shouldReceive('process')->once()->andReturns(1);
        $this->assertEquals(1, $aspect->process($point));
    }

    private function mockContainer()
    {
        $container = Mockery::mock(Container::class);
        $container->shouldReceive('make')
            ->zeroOrMoreTimes()
            ->with(StrategyInterface::class, Mockery::any())
            ->andReturn(new FlatStrategy(0));
        $container->shouldReceive('make')
            ->zeroOrMoreTimes()
            ->with(FlatStrategy::class, Mockery::any())
            ->andReturn(new FlatStrategy(0));
        $container->shouldReceive('make')
            ->zeroOrMoreTimes()
            ->with(BackoffStrategy::class, Mockery::any())
            ->andReturn(new BackoffStrategy(1));
        $container->shouldReceive('make')
            ->zeroOrMoreTimes()
            ->with(RetryBudgetInterface::class, Mockery::any())
            ->andReturn(new NoOpRetryBudget());
        ApplicationContext::setContainer($container);
    }
}
