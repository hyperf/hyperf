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
namespace Hyperf\Metric\Adapter\Prometheus;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Coordinator\Constants as Coord;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Guzzle\ClientFactory as GuzzleClientFactory;
use Hyperf\Metric\Contract\CounterInterface;
use Hyperf\Metric\Contract\GaugeInterface;
use Hyperf\Metric\Contract\HistogramInterface;
use Hyperf\Metric\Contract\MetricFactoryInterface;
use Hyperf\Metric\Exception\InvalidArgumentException;
use Hyperf\Metric\Exception\RuntimeException;
use Hyperf\Metric\MetricFactoryPicker;
use Hyperf\Utils\Coroutine;
use Hyperf\Utils\Network;
use Hyperf\Utils\Str;
use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use Swoole\Coroutine\Http\Server;

class MetricFactory implements MetricFactoryInterface
{
    private string $name;

    public function __construct(
        private ConfigInterface $config,
        private CollectorRegistry $registry,
        private GuzzleClientFactory $guzzleClientFactory,
        private StdoutLoggerInterface $logger
    ) {
        $this->name = $this->config->get('metric.default');
        $this->guardConfig();
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
        switch ($this->config->get("metric.metric.{$this->name}.mode")) {
            case Constants::SCRAPE_MODE:
                if (MetricFactoryPicker::$isCommand) {
                    $this->logger->warning('Using Prometheus scrape mode in a command. This will stop the command from terminating gracefully.');
                }
                $this->scrapeHandle();
                break;
            case Constants::PUSH_MODE:
                $this->pushHandle();
                break;
            case Constants::CUSTOM_MODE:
                $this->customHandle();
                break;
            default:
                throw new InvalidArgumentException('Unsupported Prometheus mode encountered');
        }
    }

    protected function scrapeHandle()
    {
        $host = $this->config->get("metric.metric.{$this->name}.scrape_host");
        $port = $this->config->get("metric.metric.{$this->name}.scrape_port");
        $path = $this->config->get("metric.metric.{$this->name}.scrape_path");
        $renderer = new RenderTextFormat();
        $server = new Server($host, (int) $port, false, true);
        Coroutine::create(static function () use ($server) {
            CoordinatorManager::until(Coord::WORKER_EXIT)->yield();
            $server->shutdown();
        });
        $server->handle($path, function ($request, $response) use ($renderer) {
            $response->header('Content-Type', RenderTextFormat::MIME_TYPE);
            $response->end($renderer->render($this->registry->getMetricFamilySamples()));
        });
        $server->start();
    }

    protected function pushHandle()
    {
        while (true) {
            $interval = (float) $this->config->get("metric.metric.{$this->name}.push_interval", 5);
            $host = $this->config->get("metric.metric.{$this->name}.push_host");
            $port = $this->config->get("metric.metric.{$this->name}.push_port");
            $this->doRequest("{$host}:{$port}", $this->getNamespace(), 'put');
            $workerExited = CoordinatorManager::until(Coord::WORKER_EXIT)->yield($interval);
            if ($workerExited) {
                break;
            }
        }
    }

    protected function customHandle()
    {
        CoordinatorManager::until(Coord::WORKER_EXIT)->yield(); // Yield forever
    }

    private function getNamespace(): string
    {
        $name = $this->config->get("metric.metric.{$this->name}.namespace");
        return preg_replace('#[^a-zA-Z0-9:_]#', '_', Str::snake($name));
    }

    private function guardConfig()
    {
        if ($this->config->get("metric.metric.{$this->name}.mode") == Constants::SCRAPE_MODE
            && $this->config->get('metric.use_standalone_process', true) == false) {
            throw new RuntimeException(
                "Prometheus in scrape mode must be used in conjunction with standalone process. \n Set metric.use_standalone_process to true to avoid this error."
            );
        }
    }

    private function getUri(string $address, string $job): string
    {
        if (! Str::contains($address, ['https://', 'http://'])) {
            $address = 'http://' . $address;
        }
        return $address . '/metrics/job/' . $job . '/ip/' . Network::ip() . '/pid/' . getmypid();
    }

    private function doRequest(string $address, string $job, string $method)
    {
        $url = $this->getUri($address, $job);
        $client = $this->guzzleClientFactory->create();
        $requestOptions = [
            'headers' => [
                'Content-Type' => RenderTextFormat::MIME_TYPE,
            ],
            'connect_timeout' => 10,
            'timeout' => 20,
        ];
        if ($method != 'delete') {
            $renderer = new RenderTextFormat();
            $requestOptions['body'] = $renderer->render($this->registry->getMetricFamilySamples());
        }
        $response = $client->request($method, $url, $requestOptions);
        $statusCode = $response->getStatusCode();
        if ($statusCode != 200 && $statusCode != 202) {
            $msg = 'Unexpected status code ' . $statusCode . ' received from pushgateway ' . $address . ': ' . $response->getBody();
            throw new RuntimeException($msg);
        }
    }
}
