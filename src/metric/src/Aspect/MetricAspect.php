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
use Hyperf\Metric\Adapter\Prometheus\MetricFactory;
use Hyperf\Metric\Annotation\Metric;
use Hyperf\Metric\Contract\MetricFactoryInterface;
use Psr\Container\ContainerInterface;

class MetricAspect extends AbstractAspect
{
    public const METRIC_CHANNEL = 'metric.channel';

    public array $annotations = [Metric::class];

    public ?int $priority = 1;

    /**
     * @var MetricFactory
     */
    protected MetricFactoryInterface $factory;

    public function __construct(protected ContainerInterface $container)
    {
        $this->factory = $this->container->get(MetricFactoryInterface::class);
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! $this->factory instanceof MetricFactory) {
            return $proceedingJoinPoint->process();
        }

        if (! $this->factory->channel) {
            return $proceedingJoinPoint->process();
        }

        $this->factory->channel->push(static fn () => $proceedingJoinPoint->process(), 0.0001);
    }
}
