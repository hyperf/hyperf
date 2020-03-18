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

use Hyperf\Contract\ContainerInterface;
use Hyperf\Di\Aop\AnnotationMetadata;
use Hyperf\Di\Aop\AroundInterface;
use Hyperf\Di\Aop\Pipeline;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSource;
use Hyperf\Di\Definition\ScanConfig;
use Hyperf\Retry\Annotation\AbstractRetry;
use Hyperf\Retry\Annotation\CircuitBreaker;
use Hyperf\Retry\Annotation\Retry;
use Hyperf\Retry\Aspect\RetryAnnotationAspect;
use Hyperf\Retry\BackoffStrategy;
use Hyperf\Retry\FlatStrategy;
use Hyperf\Retry\NoOpRetryBudget;
use Hyperf\Retry\Policy\TimeoutRetryPolicy;
use Hyperf\Retry\RetryBudgetInterface;
use Hyperf\Utils\ApplicationContext;
use Mockery;
use PHPUnit\Framework\TestCase;
use Swoole\Coroutine\Channel;
use Swoole\Timer;

/**
 * @internal
 * @coversNothing
 */
class RetryAnnotationAspectTest extends TestCase
{
    protected function setUp(): void
    {
        $container = new Container(new DefinitionSource([
            RetryBudgetInterface::class => NoOpRetryBudget::class,
            SleepStrategyInterface::class => flatStrategy::class,
        ], new ScanConfig()));
        ApplicationContext::setContainer($container);
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
                    $retry->sleepStrategyClass = FlatStrategy::class;
                    $this->method = [
                        AbstractRetry::class => $retry,
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
                    $retry->sleepStrategyClass = FlatStrategy::class;
                    $retry->maxAttempts = 2;
                    $this->method = [
                        AbstractRetry::class => $retry,
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
                    $retry->sleepStrategyClass = FlatStrategy::class;
                    $retry->ignoreThrowables = [\RuntimeException::class];
                    $this->method = [
                        AbstractRetry::class => $retry,
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
                    $retry->sleepStrategyClass = BackoffStrategy::class;
                    $this->method = [
                        AbstractRetry::class => $retry,
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
                    $retry->sleepStrategyClass = BackoffStrategy::class;
                    $retry->retryThrowables = [\RuntimeException::class];
                    $this->method = [
                        AbstractRetry::class => $retry,
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
                    $retry->sleepStrategyClass = FlatStrategy::class;
                    $retry->retryOnThrowablePredicate = function ($t) {
                        return $t->getMessage() === 'ok';
                    };
                    $retry->retryThrowables = [];
                    $retry->maxAttempts = 5;
                    $this->method = [
                        AbstractRetry::class => $retry,
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
                    $retry->sleepStrategyClass = FlatStrategy::class;
                    $retry->retryOnResultPredicate = function ($r) {
                        return $r <= 0;
                    };
                    $retry->retryThrowables = [];
                    $retry->maxAttempts = 5;
                    $this->method = [
                        AbstractRetry::class => $retry,
                    ];
                }
            }
        );
        $point->shouldReceive('process')->once()->andReturns(-1);
        $point->shouldReceive('process')->once()->andReturns(0);
        $point->shouldReceive('process')->once()->andReturns(1);
        $this->assertEquals(1, $aspect->process($point));
    }

    public function testCircuitBreaker()
    {
        $aspect = new RetryAnnotationAspect();
        $point = Mockery::mock(ProceedingJoinPoint::class);

        $point->shouldReceive('getAnnotationMetadata')->andReturns(
            new class() extends AnnotationMetadata {
                public $method;

                public function __construct()
                {
                    $state = Mockery::mock(
                        \Hyperf\Retry\CircuitBreakerState::class
                    );
                    $state->shouldReceive('isOpen')->twice()->andReturns(false);
                    $state->shouldReceive('isOpen')->once()->andReturns(true);
                    $retry = new CircuitBreaker(['circuitBreakerState' => $state]);
                    $retry->sleepStrategyClass = FlatStrategy::class;
                    $this->method = [
                        AbstractRetry::class => $retry,
                    ];
                }
            }
        );
        $point->shouldReceive('process')->times(2)->andThrow(new \RuntimeException('ok'));
        $point->shouldReceive('getArguments')->andReturns([]);
        $this->expectException('RuntimeException');
        $aspect->process($point);
    }

    public function testTimeout()
    {
        $aspect = new RetryAnnotationAspect();
        $point = Mockery::mock(ProceedingJoinPoint::class);

        $point->shouldReceive('getAnnotationMetadata')->andReturns(
            new class() extends AnnotationMetadata {
                public $method;

                public function __construct()
                {
                    $retry = new class() extends Retry {
                        public $timeout = 0.001;

                        public $policies = [TimeoutRetryPolicy::class];
                    };
                    $this->method = [
                        AbstractRetry::class => $retry,
                    ];
                }
            }
        );
        $point->shouldReceive('process')->atLeast(3)->andThrow(new \RuntimeException('ok'));
        $this->expectException('RuntimeException');
        $aspect->process($point);
    }

    public function testFallback()
    {
        $aspect = new RetryAnnotationAspect();
        $point = Mockery::mock(ProceedingJoinPoint::class);

        $point->shouldReceive('getAnnotationMetadata')->andReturns(
            new class() extends AnnotationMetadata {
                public $method;

                public function __construct()
                {
                    $retry = new Retry();
                    $retry->maxAttempts = 1;
                    $retry->fallback = function () {
                        return 1;
                    };
                    $retry->sleepStrategyClass = FlatStrategy::class;
                    $this->method = [
                        AbstractRetry::class => $retry,
                    ];
                }
            }
        );
        $point->shouldReceive('process')->andThrow(new \Exception());
        $point->shouldReceive('getArguments')->andReturns([]);
        $this->assertEquals(1, $aspect->process($point));
    }

    public function testPipeline()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $pipeline = new Pipeline($container);

        $aspect = new RetryAnnotationAspect();
        $aspect2 = new class() implements AroundInterface {
            public function process(ProceedingJoinPoint $proceedingJoinPoint)
            {
                return $proceedingJoinPoint->process() . '_aspect';
            }
        };

        $point = Mockery::mock(ProceedingJoinPoint::class);
        $channel = new Channel(2);
        $channel->push(true);
        $channel->push(false);
        $point->shouldReceive('processOriginalMethod')->andReturnUsing(function () use ($channel) {
            if ($channel->pop(0.001)) {
                throw new \Exception('broken');
            }
            return 'pass';
        });
        $point->shouldReceive('process')->andReturnUsing(function () use ($point) {
            $closure = $point->pipe;
            return $closure($point);
        });
        $point->shouldReceive('getArguments')->andReturns([]);
        $point->shouldReceive('getAnnotationMetadata')->andReturns(
            new class() extends AnnotationMetadata {
                public $method;

                public function __construct()
                {
                    $retry = new Retry();
                    $retry->maxAttempts = 2;
                    $retry->fallback = function () {
                        return 'fallback';
                    };
                    $retry->sleepStrategyClass = FlatStrategy::class;
                    $this->method = [
                        AbstractRetry::class => $retry,
                    ];
                }
            }
        );

        $res = $pipeline->via('process')
            ->through([$aspect, $aspect2])
            ->send($point)
            ->then(function (ProceedingJoinPoint $proceedingJoinPoint) {
                return $proceedingJoinPoint->processOriginalMethod();
            });

        $this->assertSame('pass_aspect', $res);
    }
}
