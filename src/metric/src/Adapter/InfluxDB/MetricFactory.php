<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Metric\Adapter\InfluxDB;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Guzzle\ClientFactory as GuzzleClientFactory;
use Hyperf\Metric\Adapter\Prometheus\Counter;
use Hyperf\Metric\Adapter\Prometheus\Gauge;
use Hyperf\Metric\Adapter\Prometheus\Histogram;
use Hyperf\Metric\Contract\CounterInterface;
use Hyperf\Metric\Contract\GaugeInterface;
use Hyperf\Metric\Contract\HistogramInterface;
use Hyperf\Metric\Contract\MetricFactoryInterface;
use InfluxDB\Client;
use InfluxDB\Database;
use InfluxDB\Database\RetentionPolicy;
use InfluxDB\Driver\DriverInterface;
use InfluxDB\Point;
use Prometheus\CollectorRegistry;
use Prometheus\Sample;
use Swoole\Coroutine;

class MetricFactory implements MetricFactoryInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var CollectorRegistry
     */
    private $registry;

    /**
     * @var guzzleClientFactory
     */
    private $guzzleClientFactory;

    /**
     * @var string
     */
    private $name;

    public function __construct(ConfigInterface $config, CollectorRegistry $registry, GuzzleClientFactory $guzzleClientFactory)
    {
        $this->config = $config;
        $this->registry = $registry;
        $this->guzzleClientFactory = $guzzleClientFactory;
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
        $host = $this->config->get("metric.metric.{$this->name}.host");
        $port = $this->config->get("metric.metric.{$this->name}.port");
        $username = $this->config->get("metric.metric.{$this->name}.username");
        $password = $this->config->get("metric.metric.{$this->name}.password");
        $dbname = $this->config->get("metric.metric.{$this->name}.dbname");
        $inteval = (float) $this->config->get("metric.metric.{$this->name}.push_inteval", 5);
        $create = $this->config->get("metric.metric.{$this->name}.auto_create_db");
        $client = new Client($host, $port, $username, $password);
        $guzzleClient = $this->guzzleClientFactory->create([
            'connect_timeout' => $client->getConnectTimeout(),
            'timeout' => $client->getTimeout(),
            'base_uri' => $client->getBaseURI(),
            'verify' => $client->getVerifySSL(),
        ]);
        $client->setDriver(make(DriverInterface::class, ['client' => $guzzleClient]));
        $database = $client->selectDB($dbname);
        if (! $database->exists() && $create) {
            $database->create(new RetentionPolicy($dbname, '1d', 1, true));
        }
        while (true) {
            Coroutine::sleep($inteval);
            $points = [];
            $metrics = $this->registry->getMetricFamilySamples();
            foreach ($metrics as $metric) {
                foreach ($metric->getSamples() as $sample) {
                    $points[] = $this->createPoint($sample);
                }
            }
            $result = $database->writePoints($points, Database::PRECISION_SECONDS);
        }
    }

    protected function createPoint(Sample $sample): Point
    {
        return new Point(
            $sample->getName(),
            $sample->getValue(),
            $labels = array_combine($sample->getLabelNames(), $sample->getLabelValues()),
            [],
            time()
        );
    }

    private function getNamespace(): string
    {
        return $this->config->get("metric.metric.{$this->name}.namespace");
    }
}
