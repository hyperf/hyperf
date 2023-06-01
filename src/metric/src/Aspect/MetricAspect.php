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
namespace Hyperf\Metric\Aspect;

use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Metric\Annotation\Metric;
use Psr\Container\ContainerInterface;
use Throwable;

class MetricAspect extends AbstractAspect
{
    public const METRIC_CHANNEL = 'metric.channel';

    public array $annotations = [Metric::class];

    public ?int $priority = 1;

    public function __construct(protected ContainerInterface $container)
    {
    }

    /**
     * @throws Throwable
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint): void
    {
        $this->container->get(self::METRIC_CHANNEL)->push(static function () use ($proceedingJoinPoint) {
            $proceedingJoinPoint->process();
        }, 0.0001);
    }
}
