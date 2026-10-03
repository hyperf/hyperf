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

namespace Hyperf\Metric\Adapter\InfluxDB;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Guzzle\ClientFactory as GuzzleClientFactory;
use Hyperf\Metric\Adapter\Prometheus\Counter;
use Hyperf\Metric\Adapter\Prometheus\Gauge;
use Hyperf\Metric\Adapter\Prometheus\Histogram;
use Hyperf\Metric\Contract\CounterInterface;
use Hyperf\Metric\Contract\GaugeInterface;
use Hyperf\Metric\Contract\HistogramInterface;
use Hyperf\Metric\Contract\MetricFactoryInterface;
use Hyperf\Stringable\StrCache;
use InfluxDB2\Client;
use InfluxDB2\Point;
use InfluxDB2\WriteApi;
use Prometheus\CollectorRegistry;
use Prometheus\Sample;

class MetricFactory implements MetricFactoryInterface
{
    private string $name;

    private ?Client $client = null;

    private ?WriteApi $writeApi = null;

    public function __construct(
        private ConfigInterface $config,
        private CollectorRegistry $registry,
        private GuzzleClientFactory $guzzleClientFactory
    ) {
        $this->name = $this->config->get('metric.default');
    }

    public function makeCounter(string $name, ?array $labelNames = []): CounterInterface
    {
        return new Counter(
            $this->registry,
            $this->getNamespace(),
            $name,
            'count ' . str_replace('_', ' ', $name),
            $labelNames
        );
    }

    public function makeGauge(string $name, ?array $labelNames = []): GaugeInterface
    {
        return new Gauge(
            $this->registry,
            $this->getNamespace(),
            $name,
            'gauge ' . str_replace('_', ' ', $name),
            $labelNames
        );
    }

    public function makeHistogram(string $name, ?array $labelNames = []): HistogramInterface
    {
        return new Histogram(
            $this->registry,
            $this->getNamespace(),
            $name,
            'measure ' . str_replace('_', ' ', $name),
            $labelNames
        );
    }

    public function handle(): void
    {
        $this->initializeClient();
        $interval = (float) $this->config->get("metric.metric.{$this->name}.push_interval", 5);

        while (true) {
            $workerExited = CoordinatorManager::until(Constants::WORKER_EXIT)->yield($interval);
            if ($workerExited) {
                break;
            }
            $points = [];
            $metrics = $this->registry->getMetricFamilySamples();
            foreach ($metrics as $metric) {
                foreach ($metric->getSamples() as $sample) {
                    $points[] = $this->createPoint($sample);
                }
            }
            $this->writeApi->write($points);
        }
    }

    protected function createPoint(Sample $sample): Point
    {
        $point = Point::measurement($sample->getName())
            ->addField('value', $sample->getValue())
            ->time(time());

        $labelNames = $sample->getLabelNames();
        $labelValues = $sample->getLabelValues();

        if (count($labelNames) === count($labelValues)) {
            for ($i = 0; $i < count($labelNames); ++$i) {
                $point->addTag($labelNames[$i], $labelValues[$i]);
            }
        }

        return $point;
    }

    private function initializeClient(): void
    {
        if ($this->client === null) {
            $host = $this->config->get("metric.metric.{$this->name}.host");
            $port = $this->config->get("metric.metric.{$this->name}.port");
            $token = $this->config->get("metric.metric.{$this->name}.token");
            $bucket = $this->config->get("metric.metric.{$this->name}.bucket");
            $org = $this->config->get("metric.metric.{$this->name}.org");
            $url = "http://{$host}:{$port}";

            $this->client = new Client([
                'url' => $url,
                'token' => $token,
                'bucket' => $bucket,
                'org' => $org,
            ]);

            $this->writeApi = $this->client->createWriteApi();
        }
    }

    private function getNamespace(): string
    {
        $name = $this->config->get("metric.metric.{$this->name}.namespace");
        return preg_replace('#[^a-zA-Z0-9:_]#', '_', StrCache::snake($name));
    }
}
