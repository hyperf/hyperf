# service monitoring

This component abstracts telemetry and monitoring, the important pillars of observability, to allow users to quickly integrate with existing infrastructure while avoiding vendor lock-in.A core requirement of microservice governance is service observability. As a shepherd of microservices, it is not easy to keep track of the health status of various services. Many solutions have emerged in this field in the cloud-native era. 

## Install

### Install components via Composer

```bash
composer require hyperf/metric
````

The [hyperf/metric](https://github.com/hyperf/metric) component has [Prometheus](https://prometheus.io/) dependencies installed by default. If you want to use [StatsD](https://github.com/statsd/statsd) or [InfluxDB](http://influxdb.com), you also need to execute the following commands to install the corresponding dependencies:

```bash
# StatsD required dependencies
composer require domnikl/statsd
# InfluxDB required dependencies
composer require influxdb/influxdb-php
````

### Add component configuration

If the file does not exist, execute the following command to add the `config/autoload/metric.php` configuration file:

```bash
php bin/hyperf.php vendor:publish hyperf/metric
````

## use

### Configuration

#### options

`default`: The value corresponding to `default` in the configuration file is the driver name used. The specific configuration of the driver is defined under `metric`, using the same driver as `key`.

````php
'default' u003d> env('METRIC_DRIVER', 'prometheus'),
````

* `use_standalone_process`: Whether to use `standalone monitoring process`. It is recommended to enable. After shutdown, metrics collection and reporting will be handled in the `Worker process`.

````php
'use_standalone_process' u003d> env('TELEMETRY_USE_STANDALONE_PROCESS', true),
````

* `enable_default_metric`: Whether to count default metrics. Default metrics include memory usage, system CPU load, and Swoole Server and Swoole Coroutine metrics.

````php
'enable_default_metric' u003d> env('TELEMETRY_ENABLE_DEFAULT_TELEMETRY', true),
````

`default_metric_interval`: The default metric push interval, in seconds, the same below.
````php
'default_metric_interval' u003d> env('DEFAULT_METRIC_INTERVAL', 5),
````
#### Configuring Prometheus

When using Prometheus, add the specific configuration of Prometheus to the `metric` item in the configuration file.

````php
use Hyperf\Metric\Adapter\Prometheus\Constants;

return [
'default' u003d> env('METRIC_DRIVER', 'prometheus'),
'use_standalone_process' u003d> env('TELEMETRY_USE_STANDALONE_PROCESS', true),
'enable_default_metric' u003d> env('TELEMETRY_ENABLE_DEFAULT_TELEMETRY', true),
'default_metric_interval' u003d> env('DEFAULT_METRIC_INTERVAL', 5),
'metric' u003d> [
'prometheus' u003d> [
'driver' u003d> Hyperf\Metric\Adapter\Prometheus\MetricFactory::class,
'mode' u003d> Constants::SCRAPE_MODE,
'namespace' u003d> env('APP_NAME', 'skeleton'),
'scrape_host' u003d> env('PROMETHEUS_SCRAPE_HOST', '0.0.0.0'),
'scrape_port' u003d> env('PROMETHEUS_SCRAPE_PORT', '9502'),
'scrape_path' u003d> env('PROMETHEUS_SCRAPE_PATH', '/metrics'),
'push_host' u003d> env('PROMETHEUS_PUSH_HOST', '0.0.0.0'),
'push_port' u003d> env('PROMETHEUS_PUSH_PORT', '9091'),
'push_interval' u003d> env('PROMETHEUS_PUSH_INTERVAL', 5),
],
],
];
````

Prometheus has two working modes, crawl mode and push mode (via Prometheus Pushgateway ), which can be supported by this component.

When using the crawl mode (Prometheus official recommendation), you need to set:

````php
'mode' u003d> Constants::SCRAPE_MODE
````

And configure the crawling address `scrape_host`, the crawling port `scrape_port`, and the crawling path `scrape_path`. Prometheus can pull all metrics in the form of HTTP access under the corresponding configuration.

> Note: In crawl mode, standalone process must be enabled, ie use_standalone_process u003d true.

When using push mode, you need to set:

````php
'mode' u003d> Constants::PUSH_MODE
````

And configure the push address `push_host`, push port `push_port`, push interval `push_interval`. Push mode is only recommended for offline tasks.

Because of the differences in basic settings, the above modes may not meet the needs. This component also supports custom mode. In the custom mode, the component is only responsible for the collection of indicators, and the specific reporting needs to be handled by the user.

````php
'mode' u003d> Constants::CUSTOM_MODE
````
For example, you may want to report metrics through custom routes, or store metrics in Redis, and other independent services are responsible for centralized reporting of metrics, etc. The [custom escalation](#custom escalation) section contains corresponding examples.

#### Configure StatsD

When using StatsD, add the specific configuration of StatsD to the `metric` item in the configuration file.

````php
return [
'default' u003d> env('METRIC_DRIVER', 'statd'),
'use_standalone_process' u003d> env('TELEMETRY_USE_STANDALONE_PROCESS', true),
'enable_default_metric' u003d> env('TELEMETRY_ENABLE_DEFAULT_TELEMETRY', true),
'metric' u003d> [
'statsd' u003d> [

















































































































































































































































































