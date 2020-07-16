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
use Hyperf\Metric\Adapter\Prometheus\MetricFactory as PrometheusFactory;
use Hyperf\Metric\Adapter\RemoteProxy\MetricFactory as RemoteFactory;
use Hyperf\Metric\Contract\MetricFactoryInterface;
use Hyperf\Metric\Exception\InvalidArgumentException;
use Psr\Container\ContainerInterface;

class MetricFactoryPicker
{
    /**
     * @var bool
     */
    public static $inMetricProcess = false;

    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        $useStandaloneProcess = $config->get('metric.use_standalone_process');
        // Return a proxy object for workers if user wants to use a dedicated metric process.
        if ($useStandaloneProcess && ! static::$inMetricProcess) {
            return $container->get(RemoteFactory::class);
        }

        $name = $config->get('metric.default');
        $dedicatedProcess = $config->get('metric.metric.use_standalone_process');
        $driver = $config->get("metric.metric.{$name}.driver", PrometheusFactory::class);

        $factory = $container->get($driver);
        if (! ($factory instanceof MetricFactoryInterface)) {
            throw new InvalidArgumentException(
                sprintf('The driver %s is not a valid factory.', $driver)
            );
        }
        return $factory;
    }
}
