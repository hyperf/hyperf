<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Metric\Aspect;

use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AroundInterface;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Metric\Annotation\Histogram;
use Hyperf\Metric\Contract\MetricFactoryInterface;
use Hyperf\Metric\Timer;

/**
 * @Aspect
 */
class HistogramAnnotationAspect implements AroundInterface
{
    public $classes = [];

    public $annotations = [
        Histogram::class,
    ];

    /**
     * @var MetricFactoryInterface
     */
    private $factory;

    public function __construct(MetricFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @return mixed return the value from process method of ProceedingJoinPoint, or the value that you handled
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $metadata = $proceedingJoinPoint->getAnnotationMetadata();
        /** @var Histogram $annotation */
        if ($annotation = $metadata->method[Histogram::class] ?? null) {
            $name = $annotation->name;
        } else {
            $name = $proceedingJoinPoint->methodName;
        }
        /** @var Timer $timer */
        $timer = new Timer($this
            ->factory
            ->makeHistogram($name, ['class', 'method'])
            ->with(
                $proceedingJoinPoint->className,
                $proceedingJoinPoint->methodName
            ));
        return $proceedingJoinPoint->process();
    }
}
