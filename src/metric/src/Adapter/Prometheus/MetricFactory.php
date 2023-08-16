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

use GuzzleHttp\Exception\GuzzleException;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Coordinator\Constants as Coord;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Engine\Http\ServerFactory;
use Hyperf\Engine\Http\Stream;
use Hyperf\Engine\ResponseEmitter;
use Hyperf\Guzzle\ClientFactory as GuzzleClientFactory;
use Hyperf\HttpMessage\Server\Response as HyperfResponse;
use Hyperf\Metric\Contract\CounterInterface;
use Hyperf\Metric\Contract\GaugeInterface;
use Hyperf\Metric\Contract\HistogramInterface;
use Hyperf\Metric\Contract\MetricFactoryInterface;
use Hyperf\Metric\Exception\InvalidArgumentException;
use Hyperf\Metric\Exception\RuntimeException;
use Hyperf\Metric\MetricFactoryPicker;
use Hyperf\Stringable\Str;
use Hyperf\Support\Network;
use Prometheus\CollectorRegistry;
use Prometheus\Exception\MetricsRegistrationException;
use Prometheus\RenderTextFormat;
use Psr\Http\Message\RequestInterface;

class MetricFactory implements MetricFactoryInterface
{
    private string $name;

    public function __construct(
        private ConfigInterface $config,
        private CollectorRegistry $registry,
        private GuzzleClientFactory $guzzleClientFactory,
        private StdoutLoggerInterface $logger,
        private ServerFactory $factory
    ) {
        $this->name = $this->config->get('metric.default');
    }

    /**
     * @throws MetricsRegistrationException
     */
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

    /**
     * @throws MetricsRegistrationException
     */
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

    /**
     * @throws MetricsRegistrationException
     */
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

    /**
     * @throws GuzzleException
     */
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

    protected function scrapeHandle(): void
    {
        $host = $this->config->get("metric.metric.{$this->name}.scrape_host");
        $port = $this->config->get("metric.metric.{$this->name}.scrape_port");

        foreach ($this->config->get('server.servers', []) as $item) {
            if (isset($item['port']) && $item['port'] == $port) {
                $this->logger->error(sprintf('Your service has the same port %s as metric scrape mode, which may cause service or scrape mode failure.', $port));
            }
        }

        $server = $this->factory->make($host, (int) $port);

        Coroutine::create(static function () use ($server) {
            CoordinatorManager::until(Coord::WORKER_EXIT)->yield();
            $server->close();
        });

        $emitter = new ResponseEmitter($this->logger);
        $renderer = new RenderTextFormat();

        $server->handle(function (RequestInterface $request, mixed $connection) use ($emitter, $renderer) {
            $response = new HyperfResponse();
            $response = $response->withHeader('Content-Type', RenderTextFormat::MIME_TYPE)
                ->withBody(new Stream($renderer->render($this->registry->getMetricFamilySamples())));
            $emitter->emit($response, $connection);
        });

        $server->start();
    }

    /**
     * @throws GuzzleException
     */
    protected function pushHandle(): void
    {
        while (true) {
            $interval = (float) $this->config->get("metric.metric.{$this->name}.push_interval", 5);
            $host = $this->config->get("metric.metric.{$this->name}.push_host");
            $port = $this->config->get("metric.metric.{$this->name}.push_port");
            $this->doRequest("{$host}:{$port}", $this->getNamespace());
            $workerExited = CoordinatorManager::until(Coord::WORKER_EXIT)->yield($interval);
            if ($workerExited) {
                break;
            }
        }
    }

    protected function customHandle(): void
    {
        CoordinatorManager::until(Coord::WORKER_EXIT)->yield(); // Yield forever
    }

    private function getNamespace(): string
    {
        $name = $this->config->get("metric.metric.{$this->name}.namespace");
        return preg_replace('#[^a-zA-Z0-9:_]#', '_', Str::snake($name));
    }

    private function getUri(string $address, string $job): string
    {
        if (! Str::contains($address, ['https://', 'http://'])) {
            $address = 'http://' . $address;
        }
        return $address . '/metrics/job/' . $job . '/ip/' . Network::ip() . '/pid/' . getmypid();
    }

    /**
     * @throws GuzzleException
     */
    private function doRequest(string $address, string $job): void
    {
        $url = $this->getUri($address, $job);
        $requestOptions = [
            'headers' => [
                'Content-Type' => RenderTextFormat::MIME_TYPE,
            ],
            'connect_timeout' => 10,
            'timeout' => 20,
            'body' => (new RenderTextFormat())->render($this->registry->getMetricFamilySamples()),
        ];

        $response = $this->guzzleClientFactory->create()->request('put', $url, $requestOptions);
        $statusCode = $response->getStatusCode();

        if ($statusCode != 200 && $statusCode != 202) {
            $msg = 'Unexpected status code ' . $statusCode . ' received from pushgateway ' . $address . ': ' . $response->getBody();
            throw new RuntimeException($msg);
        }
    }
}
