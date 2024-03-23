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

namespace Hyperf\Metric\Adapter\RemoteProxy;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Metric\Contract\MetricCollectorInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class MetricCollectorFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): MetricCollectorInterface
    {
        $config = $container->get(ConfigInterface::class);

        return new MetricCollector(
            (int) $config->get('metric.buffer_size', 200)
        );
    }
}
