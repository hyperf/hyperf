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
namespace Hyperf\Metric;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Metric\Adapter\Prometheus\MetricFactory as PrometheusFactory;
use Hyperf\Metric\Adapter\RemoteProxy\MetricFactory as RemoteFactory;
use Hyperf\Metric\Contract\MetricFactoryInterface;
use Hyperf\Metric\Exception\InvalidArgumentException;
use Hyperf\Process\ProcessCollector;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class MetricFactoryPicker
{
    public static bool $inMetricProcess = false;

    public static bool $isCommand = false;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): MetricFactoryInterface
    {
        // All other metric factories needs to be run in coroutine context
        if (! Coroutine::inCoroutine()) {
            return $container->get(Adapter\NoOp\MetricFactory::class);
        }

        $config = $container->get(ConfigInterface::class);
        $useStandaloneProcess = $config->get('metric.use_standalone_process', true);

        // misconfiguration.
        if ($useStandaloneProcess && ! static::$isCommand && empty(ProcessCollector::all())) {
            return $container->get(Adapter\NoOp\MetricFactory::class);
        }

        // Return a proxy object for workers if user wants to use a dedicated metric process.
        if ($useStandaloneProcess && ! static::$inMetricProcess && ! static::$isCommand) {
            return $container->get(RemoteFactory::class);
        }

        $name = $config->get('metric.default');
        $driver = $config->get("metric.metric.{$name}.driver", PrometheusFactory::class);

        $factory = $container->get($driver);
        if (! $factory instanceof MetricFactoryInterface) {
            throw new InvalidArgumentException(
                sprintf('The driver %s is not a valid factory.', $driver)
            );
        }
        return $factory;
    }
}
