# Service Monitoring

A core requirement of microservice governance is service observability. As a shepherd of microservices, it is not easy to keep track of the health status of each service. Many solutions have emerged in this field in the cloud-native era. This component abstracts the important pillars of observability—telemetry and monitoring—making it convenient for users to combine quickly with existing infrastructure, while avoiding vendor lock-in.

## Installation

### Install component via Composer

```bash
composer require hyperf/metric
```

Metric supports [Prometheus](https://prometheus.io/), [StatsD](https://github.com/statsd/statsd), and [InfluxDB](http://influxdb.com). You can execute the following commands to install the corresponding dependencies:

```bash
# Prometheus
composer require promphp/prometheus_client_php
# StatsD dependencies
composer require domnikl/statsd
# InfluxDB dependencies
composer require influxdb/influxdb-php 
```

### Add component configuration

If the file does not exist, you can execute the following command to add the `config/autoload/metric.php` configuration file:

```bash
php bin/hyperf.php vendor:publish hyperf/metric
```

## Usage

### Configuration

#### Options

`default`: The value corresponding to `default` in the configuration file is the name of the driver used. The specific configuration of the driver is defined under the `metric` item, using the same driver as the `key`.

```php
'default' => env('METRIC_DRIVER', 'prometheus'),
```

* `use_standalone_process`: Whether to use a `standalone monitoring process`. Enabling it is recommended. If disabled, metric collection and reporting will be handled in the `Worker process`.

```php
'use_standalone_process' => env('TELEMETRY_USE_STANDALONE_PROCESS', true),
```

* `enable_default_metric`: Whether to collect default metrics. Default metrics include memory usage, system CPU load, as well as Swoole Server metrics and Swoole Coroutine metrics.

```php
'enable_default_metric' => env('TELEMETRY_ENABLE_DEFAULT_TELEMETRY', true),
```

`default_metric_interval`: The push period for default metrics, in seconds (the same applies below).
```php
'default_metric_interval' => env('DEFAULT_METRIC_INTERVAL', 5),
```

#### Configure Prometheus

When using Prometheus, add the specific configuration of Prometheus to the `metric` item in the configuration file.

```php
use Hyperf\Metric\Adapter\Prometheus\Constants;

return [
    'default' => env('METRIC_DRIVER', 'prometheus'),
    'use_standalone_process' => env('TELEMETRY_USE_STANDALONE_PROCESS', true),
    'enable_default_metric' => env('TELEMETRY_ENABLE_DEFAULT_TELEMETRY', true),
    'default_metric_interval' => env('DEFAULT_METRIC_INTERVAL', 5),
    'metric' => [
        'prometheus' => [
            'driver' => Hyperf\Metric\Adapter\Prometheus\MetricFactory::class,
            'mode' => Constants::SCRAPE_MODE,
            'namespace' => env('APP_NAME', 'skeleton'),
            'scrape_host' => env('PROMETHEUS_SCRAPE_HOST', '0.0.0.0'),
            'scrape_port' => env('PROMETHEUS_SCRAPE_PORT', '9502'),
            'scrape_path' => env('PROMETHEUS_SCRAPE_PATH', '/metrics'),
            'push_host' => env('PROMETHEUS_PUSH_HOST', '0.0.0.0'),
            'push_port' => env('PROMETHEUS_PUSH_PORT', '9091'),
            'push_interval' => env('PROMETHEUS_PUSH_INTERVAL', 5),
        ],
    ],
];
```

Prometheus has two operating modes: scrape mode and push mode (via Prometheus Pushgateway). This component supports both.

When using scrape mode (officially recommended by Prometheus), you need to set:

```php
'mode' => Constants::SCRAPE_MODE
```

And configure the scraping address `scrape_host`, scraping port `scrape_port`, and scraping path `scrape_path`. Prometheus can pull all metrics in HTTP access form under the corresponding configuration.

> Note: In asynchronous style, scrape mode must enable an independent process, i.e., `use_standalone_process = true`.

When using push mode, you need to set:

```php
'mode' => Constants::PUSH_MODE
```

And configure the pushing address `push_host`, pushing port `push_port`, and pushing interval `push_interval`. Push mode is only recommended for offline tasks.

Because of the differences in basic settings, the above modes may not satisfy all requirements. This component also supports a custom mode. In custom mode, the component is only responsible for the collection of metrics, and specific reporting needs to be handled by the user.

```php
'mode' => Constants::CUSTOM_MODE
```

For example, you might want to report metrics via a custom route, or hope to store metrics in Redis, with other independent services responsible for centralized reporting of metrics. The [Custom Reporting](#custom-reporting) section contains corresponding examples.

#### Configure StatsD

When using StatsD, add the specific configuration of StatsD to the `metric` item in the configuration file.

```php
return [
    'default' => env('METRIC_DRIVER', 'statd'),
    'use_standalone_process' => env('TELEMETRY_USE_STANDALONE_PROCESS', true),
    'enable_default_metric' => env('TELEMETRY_ENABLE_DEFAULT_TELEMETRY', true),
    'metric' => [
        'statsd' => [
            'driver' => Hyperf\Metric\Adapter\StatsD\MetricFactory::class,
            'namespace' => env('APP_NAME', 'skeleton'),
            'udp_host' => env('STATSD_UDP_HOST', '127.0.0.1'),
            'udp_port' => env('STATSD_UDP_PORT', '8125'),
            'enable_batch' => env('STATSD_ENABLE_BATCH', true),
            'push_interval' => env('STATSD_PUSH_INTERVAL', 5),
            'sample_rate' => env('STATSD_SAMPLE_RATE', 1.0),
        ],
    ],
];
```

StatsD currently only supports UDP mode. It requires configuring the UDP address `udp_host`, UDP port `udp_port`, whether to batch push `enable_batch` (to reduce the number of requests), batch push interval `push_interval`, and sample rate `sample_rate`.

#### Configure InfluxDB

When using InfluxDB, add the specific configuration of InfluxDB to the `metric` item in the configuration file.

```php
return [
    'default' => env('METRIC_DRIVER', 'influxdb'),
    'use_standalone_process' => env('TELEMETRY_USE_STANDALONE_PROCESS', true),
    'enable_default_metric' => env('TELEMETRY_ENABLE_DEFAULT_TELEMETRY', true),
    'metric' => [
        'influxdb' => [
            'driver' => Hyperf\Metric\Adapter\InfluxDB\MetricFactory::class,
            'namespace' => env('APP_NAME', 'skeleton'),
            'host' => env('INFLUXDB_HOST', '127.0.0.1'),
            'port' => env('INFLUXDB_PORT', '8086'),
            'username' => env('INFLUXDB_USERNAME', ''),
            'password' => env('INFLUXDB_PASSWORD', ''),
            'dbname' => env('INFLUXDB_DBNAME', true),
            'push_interval' => env('INFLUXDB_PUSH_INTERVAL', 5),
        ],
    ],
];
```

InfluxDB uses the default HTTP mode. It requires configuring the address `host`, UDP port `port` (Note: InfluxDB usually uses HTTP port 8086), username `username`, password `password`, `dbname` table, and batch push interval `push_interval`.

### Basic Abstraction

The telemetry component abstracts three commonly used data types to ensure decoupling from specific implementations.

The three types are:

Counter: Used to describe a metric that monotonically increases. E.g., HTTP request count.

```php
interface CounterInterface
{
    public function with(string ...$labelValues): self;

    public function add(int $delta);
}
```

Gauge: Used to describe a metric that increases or decreases over time. E.g., the number of available connections in the connection pool.

```php
interface GaugeInterface
{
    public function with(string ...$labelValues): self;

    public function set(float $value);
    
    public function add(float $delta);
}
```

Histogram: Used to describe the statistical distribution produced after continuous observation of an event, usually represented as percentiles or buckets. E.g., HTTP request latency.

```php
interface HistogramInterface
{
    public function with(string ...$labelValues): self;

    public function put(float $sample);
}
```

### Configure middleware

After configuring the driver, just configure the middleware to enable the Histogram statistics function for requests.
Open the `config/autoload/middlewares.php` file, the example shows enabling the middleware in the `http` Server.

```php
<?php

declare(strict_types=1);

return [
    'http' => [
        \Hyperf\Metric\Middleware\MetricMiddleware::class,
    ],
];
```
> The statistics dimensions in this middleware include `request_status`, `request_path`, `request_method`. If you have too many `request_path`s, it is recommended to rewrite this middleware and remove the `request_path` dimension, otherwise excessive cardinality will lead to memory overflow.

### Custom Usage

Telemetry via HTTP middleware is just the tip of the iceberg of this component's purpose. You can inject the `Hyperf\Metric\Contract\MetricFactoryInterface` class to telemetry business data yourself. For example: the number of orders created, the number of ad clicks, etc.

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Order;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Metric\Contract\MetricFactoryInterface;

class IndexController extends AbstractController
{
    #[Inject]
    private MetricFactoryInterface $metricFactory;

    public function create(Order $order)
    {
        $counter = $this->metricFactory->makeCounter('order_created', ['order_type']);
        $counter->with($order->type)->add(1);
        // Order logic...
    }
}
```

`MetricFactoryInterface` contains the following factory methods to generate the corresponding three basic statistical types.

```php
public function makeCounter($name, $labelNames): CounterInterface;

public function makeGauge($name, $labelNames): GaugeInterface;

public function makeHistogram($name, $labelNames): HistogramInterface;
```

The above example is to collect metrics generated within the scope of a request. Sometimes the metrics we need to collect are oriented towards the complete lifecycle, such as counting the length of an asynchronous queue or the number of inventory products. In this scenario, you can listen to the `MetricFactoryReady` event.

```php
<?php

declare(strict_types=1);

namespace App\Listener;

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Metric\Event\MetricFactoryReady;
use Psr\Container\ContainerInterface;
use Redis;

class OnMetricFactoryReady implements ListenerInterface
{
    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function listen(): array
    {
        return [
            MetricFactoryReady::class,
        ];
    }

    public function process(object $event)
    {
        $redis = $this->container->get(Redis::class);
        $gauge = $event
                    ->factory
                    ->makeGauge('queue_length', ['driver'])
                    ->with('redis');
        while (true) {
            $length = $redis->llen('queue');
            $gauge->set($length);
            sleep(1);
        }
    }
}
```

> From an engineering perspective, it is not very appropriate to query the queue length directly from Redis. You should use the `info()` method under the queue driver's `DriverInterface` interface to get the queue length. This is only a simple demonstration. You can find the complete example in the `src/Listener` folder of this component's source code.

### Annotation

You can use `#[Counter(name="stat_name_here")]` and `#[Histogram(name="stat_name_here")]` to count the invocation times and runtime of aspects.

For the use of annotations, please refer to the [Annotation Chapter](annotation.md).

### Custom Histogram Bucket

> This section only applies to the Prometheus driver

When you are using Histogram in Prometheus, there are sometimes requirements for custom Buckets. You can rely on injecting Registry and registering Histogram by yourself before the service starts, and set the required Buckets. Later, when using `MetricFactory`, it will call the Histogram of the same name you registered. An example is as follows:

```php
<?php

namespace App\Listener;

use Hyperf\Config\Annotation\Value;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeMainServerStart;
use Prometheus\CollectorRegistry;

class OnMainServerStart implements ListenerInterface
{
    protected $registry;

    public function __construct(CollectorRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function listen(): array
    {
        return [
            BeforeMainServerStart::class,
        ];
    }

    public function process(object $event)
    {
        $this->registry->registerHistogram(
            config("metric.metric.prometheus.namespace"), 
            'test',
            'help_message', 
            ['labelName'], 
            [0.1, 1, 2, 3.5]
        );
    }
}
```
After that, when you use `$metricFactory->makeHistogram('test')`, it will return the Histogram you registered in advance.

### Custom Reporting

> This section only applies to the Prometheus driver

After setting the Prometheus driver operating mode of the component to custom mode (`Constants::CUSTOM_MODE`), you can freely handle metric reporting. In this section, we show how to store metrics in Redis, and then add a new HTTP route in the Worker to return the metrics rendered by Prometheus.

#### Use Redis to store metrics

The storage medium for metrics is defined by the `Prometheus\Storage\Adapter` interface. In-memory storage is used by default. We can change it to Redis storage in `config/autoload/dependencies.php`.

```php
<?php

return [
    Prometheus\Storage\Adapter::class => Hyperf\Metric\Adapter\Prometheus\RedisStorageFactory::class,
];
```

#### Add /metrics route in Worker

Add Prometheus route in config/routes.php.

> Note that if you want to get metrics in the Worker, you need to handle the state sharing problem between Workers yourself. One way is to store the state in Redis as described above.

```php
<?php

use Hyperf\HttpServer\Router\Router;

Router::get('/metrics', function(){
    $registry = Hyperf\Context\ApplicationContext::getContainer()->get(Prometheus\CollectorRegistry::class);
    $renderer = new Prometheus\RenderTextFormat();
    return $renderer->render($registry->getMetricFamilySamples());
});
```

## Create Console in Grafana

> This section only applies to the Prometheus driver

If you enable default metrics, `Hyperf/Metric` has prepared an out-of-the-box Grafana console for you. Download the console [json file](https://cdn.jsdelivr.net/gh/hyperf/hyperf/src/metric/grafana.json) and import it into Grafana to use it.

![grafana](imgs/grafana.png)

## Precautions

- If you need to use this component to collect metrics in `hyperf/command` custom commands, you need to add the command line argument when starting the command: `--enable-event-dispatcher`.
