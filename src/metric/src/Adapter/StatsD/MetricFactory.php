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

namespace Hyperf\Metric\Adapter\StatsD;

use Domnikl\Statsd\Client;
use Domnikl\Statsd\Connection;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Metric\Adapter\Statsd\Gauge;
use Hyperf\Metric\Adapter\Statsd\Histogram;
use Hyperf\Metric\Contract\CounterInterface;
use Hyperf\Metric\Contract\GaugeInterface;
use Hyperf\Metric\Contract\HistogramInterface;
use Hyperf\Metric\Contract\MetricFactoryInterface;
use Swoole\Coroutine;

class MetricFactory implements MetricFactoryInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var Client
     */
    private $client;

    /**
     * GuzzleClientFactory.
     */
    private $guzzleClientFactory;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
        $this->client = make(Client::class, [
            'connection' => $this->getConnection(),
            'namespace' => $this->getNamespace(),
            'sampleRateAllMetrics' => $this->getSampleRate(),
        ]);
    }

    public function makeCounter(string $name, ?array $labelNames = []): CounterInterface
    {
        return new Counter(
            $this->client,
            $name,
            $this->getSampleRate(),
            $labelNames
        );
    }

    public function makeGauge(string $name, ?array $labelNames = []): GaugeInterface
    {
        return new Gauge(
            $this->client,
            $name,
            $this->getSampleRate(),
            $labelNames
        );
    }

    public function makeHistogram(string $name, ?array $labelNames = []): HistogramInterface
    {
        return new Histogram(
            $this->client,
            $name,
            $this->getSampleRate(),
            $labelNames
        );
    }

    public function handle(): void
    {
        $name = $this->config->get('metric.default');
        $inteval = (float) $this->config->get("metric.metric.{$name}.push_inteval", 5);
        $batchEnabled = $this->config->get("metric.metric.{$name}.enable_batch") == true;
        // Block handle from returning.
        do {
            if ($batchEnabled) {
                $this->client->startBatch();
                Coroutine::sleep((int) $inteval);
                $this->client->endBatch();
            } else {
                Coroutine::sleep(5000);
            }
        } while (true);
    }

    protected function getConnection(): Connection
    {
        $name = $this->config->get('metric.default');
        $host = $this->config->get("metric.metric.{$name}.udp_host");
        $port = $this->config->get("metric.metric.{$name}.udp_port");
        return make(Connection::class, [
            'host' => $host,
            'port' => (int) $port,
            'timeout' => null,
            'persistent' => true,
        ]);
    }

    protected function getNamespace(): string
    {
        $name = $this->config->get('metric.default');
        return $this->config->get("metric.metric.{$name}.namespace");
    }

    protected function getSampleRate(): float
    {
        $name = $this->config->get('metric.default');
        return $this->config->get("metric.metric.{$name}.sample_rate", 1.0);
    }
}
