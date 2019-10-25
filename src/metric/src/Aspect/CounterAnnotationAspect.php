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
use Hyperf\Metric\Annotation\Counter;
use Hyperf\Metric\Contract\MetricFactoryInterface;

/**
 * @Aspect
 */
class CounterAnnotationAspect implements AroundInterface
{
    public $classes = [];

    public $annotations = [
        Counter::class,
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
        /** @var Counter $annotation */
        if ($annotation = $metadata->method[Counter::class] ?? null) {
            $name = $annotation->name;
        } else {
            $name = $proceedingJoinPoint->methodName;
        }
        $counter = $this->factory->makeCounter($name, ['class', 'method']);
        $result = $proceedingJoinPoint->process();
        $counter
            ->with(
                $proceedingJoinPoint->className,
                $proceedingJoinPoint->methodName
            )
            ->add(1);
        return $result;
    }
}
