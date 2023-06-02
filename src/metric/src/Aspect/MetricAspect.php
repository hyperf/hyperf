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

use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Engine\Channel;
use Hyperf\Metric\Adapter\Prometheus\Constants;
use Hyperf\Metric\Annotation\Metric;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
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
        if (is_null($channel = $this->getChannel())) {
            $proceedingJoinPoint->process();

            return;
        }

        $channel->push(static function () use ($proceedingJoinPoint) {
            $proceedingJoinPoint->process();
        }, 0.0001);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getChannel(): ?Channel
    {
        if (! $this->container->has(ConfigInterface::class) || ! $this->container->has(self::METRIC_CHANNEL)) {
            return null;
        }

        if ($this->container->get(ConfigInterface::class)->get('metric.metric.prometheus.mode') !== Constants::CUSTOM_MODE) {
            return null;
        }

        $channel = $this->container->get(self::METRIC_CHANNEL);

        if (! $channel instanceof Channel) {
            return null;
        }

        return $channel;
    }
}
