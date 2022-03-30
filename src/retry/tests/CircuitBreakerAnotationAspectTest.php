<?php

namespace HyperfTest\Retry;

use Hyperf\Di\Aop\AnnotationMetadata;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSource;
use Hyperf\Retry\Annotation\AbstractRetry;
use Hyperf\Retry\Annotation\CircuitBreaker;
use Hyperf\Retry\Aspect\RetryAnnotationAspect;
use Hyperf\Retry\CircuitBreakerState;
use Hyperf\Retry\FlatStrategy;
use Hyperf\Retry\NoOpRetryBudget;
use Hyperf\Retry\RetryBudgetInterface;
use Hyperf\Utils\ApplicationContext;
use HyperfTest\Retry\Stub\Foo;
use Mockery;
use PHPUnit\Framework\TestCase;

class CircuitBreakerAnotationAspectTest extends TestCase
{
    protected function setUp(): void
    {
        $container = new Container(new DefinitionSource([]));

        ApplicationContext::setContainer($container);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testFallbackWhenStateOpenFirst()
    {
        $aspect = new RetryAnnotationAspect();
        $point = Mockery::mock(ProceedingJoinPoint::class);

        $point->shouldReceive('getAnnotationMetadata')->andReturns(
            new class() extends AnnotationMetadata {
                public $method;

                public function __construct()
                {
                    $state = Mockery::mock(
                        CircuitBreakerState::class
                    );
                    $state->shouldReceive('isOpen')->andReturns(true);
                    $state->shouldReceive('open')->andReturns();
                    $retry = new CircuitBreaker(['circuitBreakerState' => $state]);
                    $retry->sleepStrategyClass = FlatStrategy::class;
                    $retry->fallback = Foo::class . '@fallbackWithThrowable';
                    $this->method = [
                        AbstractRetry::class => $retry,
                    ];
                }
            }
        );
        $point->shouldReceive('getArguments')->andReturns([$string = uniqid()]);
        $result = $aspect->process($point);

        self::assertEquals($string.':fallback', $result);
    }
}
