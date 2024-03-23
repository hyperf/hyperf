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

use Domnikl\Statsd\Connection;
use Domnikl\Statsd\Connection\UdpSocket;
use Hyperf\Metric\Adapter\RemoteProxy\MetricCollectorFactory;
use Hyperf\Metric\Aspect\CounterAnnotationAspect;
use Hyperf\Metric\Aspect\HistogramAnnotationAspect;
use Hyperf\Metric\Contract\MetricCollectorInterface;
use Hyperf\Metric\Contract\MetricFactoryInterface;
use Hyperf\Metric\Listener\MetricBufferWatcher;
use Hyperf\Metric\Listener\OnBeforeHandle;
use Hyperf\Metric\Listener\OnCoroutineServerStart;
use Hyperf\Metric\Listener\OnMetricFactoryReady;
use Hyperf\Metric\Listener\OnPipeMessage;
use Hyperf\Metric\Listener\OnWorkerStart;
use Hyperf\Metric\Process\MetricProcess;
use InfluxDB\Driver\DriverInterface;
use InfluxDB\Driver\Guzzle;
use Prometheus\Storage\Adapter;
use Prometheus\Storage\InMemory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                MetricFactoryInterface::class => MetricFactoryPicker::class,
                Adapter::class => InMemory::class,
                Connection::class => UdpSocket::class,
                DriverInterface::class => Guzzle::class,
                MetricCollectorInterface::class => MetricCollectorFactory::class,
            ],
            'aspects' => [
                CounterAnnotationAspect::class,
                HistogramAnnotationAspect::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for metric component.',
                    'source' => __DIR__ . '/../publish/metric.php',
                    'destination' => BASE_PATH . '/config/autoload/metric.php',
                ],
            ],
            'listeners' => [
                OnPipeMessage::class,
                OnMetricFactoryReady::class,
                OnBeforeHandle::class,
                OnWorkerStart::class,
                OnCoroutineServerStart::class,
                MetricBufferWatcher::class,
            ],
            'processes' => [
                MetricProcess::class,
            ],
        ];
    }
}
