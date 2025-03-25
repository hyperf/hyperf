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

namespace HyperfTest\SingleFlight;

use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\SingleFlight\Annotation\SingleFlight;
use Hyperf\SingleFlight\Aspect\SingleFlightAspect;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class AspectTest extends TestCase
{
    protected function tearDown(): void
    {
        AnnotationCollector::clear('SingleFlightTestClass');
    }

    public function testBarrierKey()
    {
        $aspect = new SingleFlightAspect();
        $reflection = new ReflectionClass($aspect);
        $method = $reflection->getMethod('barrierKey');
        $method->setAccessible(true);

        $proceedingJoinPoint = $this->createMock(ProceedingJoinPoint::class);
        $proceedingJoinPoint->className = 'SingleFlightTestClass';
        $proceedingJoinPoint->methodName = 'testMethod';
        $proceedingJoinPoint->arguments = ['keys' => ['arg1' => 'arg1', 'arg2' => 'arg2']];

        $annotation = new SingleFlight('#{arg1}_#{arg2}');
        AnnotationCollector::collectMethod('SingleFlightTestClass', 'testMethod', SingleFlight::class, $annotation);
        $result = $method->invoke($aspect, $proceedingJoinPoint);

        $this->assertEquals('arg1_arg2', $result);
    }
}