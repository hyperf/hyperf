# 服务监控

微服务治理的一个核心需求便是服务可观察性。作为微服务的牧羊人，要做到时刻掌握各项服务的健康状态，并非易事。云原生时代这一领域内涌现出了诸多解决方案。本组件对可观察性当中的重要支柱遥测与监控进行了抽象，方便使用者与既有基础设施快速结合，同时避免供应商锁定。

## 安装

### 通过 Composer 安装组件

```bash
composer require hyperf/metric
```

Metric 支持 [Prometheus](https://prometheus.io/)、[StatsD](https://github.com/statsd/statsd) 和 [InfluxDB](http://influxdb.com)，可以执行下面的命令安装对应的依赖：

```bash
# Prometheus
composer require promphp/prometheus_client_php
# StatsD 所需依赖
composer require domnikl/statsd
# InfluxDB 所需依赖 
composer require influxdb/influxdb-php 
```

### 增加组件配置

如文件不存在，可执行下面的命令增加 `config/autoload/metric.php` 配置文件：

```bash
php bin/hyperf.php vendor:publish hyperf/metric
```

## 使用

### 配置

#### 选项

`default`：配置文件内的 `default` 对应的值则为使用的驱动名称。驱动的具体配置在 `metric` 项下定义，使用与 `key` 相同的驱动。

```php
'default' => env('METRIC_DRIVER', 'prometheus'),
```

* `use_standalone_process`: 是否使用 `独立监控进程`。推荐开启。关闭后将在 `Worker 进程` 中处理指标收集与上报。

```php
'use_standalone_process' => env('TELEMETRY_USE_STANDALONE_PROCESS', true),
```

* `enable_default_metric`: 是否统计默认指标。默认指标包括内存占用、系统 CPU 负载以及 Swoole Server 指标和 Swoole Coroutine 指标。

```php
'enable_default_metric' => env('TELEMETRY_ENABLE_DEFAULT_TELEMETRY', true),
```

`default_metric_interval`: 默认指标推送周期，单位为秒，下同。
```php
'default_metric_interval' => env('DEFAULT_METRIC_INTERVAL', 5),
```
#### 配置 Prometheus

使用 Prometheus 时，在配置文件中的 `metric` 项增加 Prometheus 的具体配置。

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

Prometheus 有两种工作模式，爬模式与推模式（通过 Prometheus Pushgateway ），本组件均可支持。

使用爬模式（Prometheus 官方推荐）时需设置：

```php
'mode' => Constants::SCRAPE_MODE
```

并配置爬取地址 `scrape_host`、爬取端口 `scrape_port`、爬取路径 `scrape_path`。Prometheus 可以在对应配置下以 HTTP 访问形式拉取全部指标。

> 注意：异步风格下，爬模式必须启用独立进程，即 `use_standalone_process = true`。

使用推模式时需设置：

```php
'mode' => Constants::PUSH_MODE
```

并配置推送地址 `push_host`、推送端口 `push_port`、推送间隔 `push_interval`。只建议离线任务使用推模式。

因为基础设置的差异性，可能以上模式都无法满足需求。本组件还支持自定义模式。在自定义模式下，组件只负责指标的收集，具体的上报需要使用者自行处理。

```php
'mode' => Constants::CUSTOM_MODE
```
例如，您可能希望通过自定义的路由上报指标，或希望将指标存入 Redis 中，由其他独立服务负责指标的集中上报等。[自定义上报](#自定义上报)一节包含了相应的示例。

#### 配置 StatsD

使用 StatsD 时，在配置文件中的 `metric` 项增加 StatsD 的具体配置。

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

StatsD 目前只支持 UDP 模式，需要配置 UDP 地址 `udp_host`，UDP 端口 `udp_port`、是否批量推送 `enable_batch`（减少请求次数）、批量推送间隔 `push_interval` 以及采样率 `sample_rate` 。

#### 配置 InfluxDB

使用 InfluxDB 时，在配置文件中的 `metric` 项增加 InfluxDB 的具体配置。

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

InfluxDB 使用默认的 HTTP 模式，需要配置地址 `host`，UDP 端口 `port`、用户名  `username`、密码 `password`、`dbname` 数据表以及批量推送间隔 `push_interval`。

### 基本抽象

遥测组件对常用的三种数据类型进行了抽象，以确保解耦具体实现。

三种类型分别为：

计数器(Counter): 用于描述单向递增的某种指标。如 HTTP 请求计数。

```php
interface CounterInterface
{
    public function with(string ...$labelValues): self;

    public function add(int $delta);
}
```

测量器(Gauge)：用于描述某种随时间发生增减变化的指标。如连接池内的可用连接数。

```php
interface GaugeInterface
{
    public function with(string ...$labelValues): self;

    public function set(float $value);

    public function add(float $delta);
}
```

* 直方图(Histogram)：用于描述对某一事件的持续观测后产生的统计学分布，通常表示为百分位数或分桶。如 HTTP 请求延迟。

```php
interface HistogramInterface
{
    public function with(string ...$labelValues): self;

    public function put(float $sample);
}
```

### 配置中间件

配置完驱动之后，只需配置一下中间件就能启用请求 Histogram 统计功能。
打开 `config/autoload/middlewares.php` 文件，示例为在 `http` Server 中启用中间件。

```php
<?php

declare(strict_types=1);

return [
    'http' => [
        \Hyperf\Metric\Middleware\MetricMiddleware::class,
    ],
];
```
> 本中间件中统计维度包含 `request_status`、`request_path`、`request_method`。如果您的 `request_path` 过多，则建议重写本中间件，去掉 `request_path` 维度，否则过高的基数会导致内存溢出。

### 自定义使用

通过 HTTP 中间件遥测仅仅是本组件用途的冰山一角，您可以注入 `Hyperf\Metric\Contract\MetricFactoryInterface` 类来自行遥测业务数据。比如：创建的订单数量、广告的点击数量等。

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
        // 订单逻辑...
    }

}
```

`MetricFactoryInterface` 中包含如下工厂方法来生成对应的三种基本统计类型。

```php
public function makeCounter($name, $labelNames): CounterInterface;

public function makeGauge($name, $labelNames): GaugeInterface;

public function makeHistogram($name, $labelNames): HistogramInterface;
```

上述例子是统计请求范围内的产生的指标。有时候我们需要统计的指标是面向完整生命周期的，比如统计异步队列长度或库存商品数量。此种场景下可以监听 `MetricFactoryReady` 事件。

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

> 工程上讲，直接从 Redis 查询队列长度不太合适，应该通过队列驱动 `DriverInterface` 接口下的 `info()` 方法来获取队列长度。这里只做简易演示。您可以在本组件源码的`src/Listener` 文件夹下找到完整例子。

### 注解

您可以使用 `#[Counter(name="stat_name_here")]` 和 `#[Histogram(name="stat_name_here")]` 来统计切面的调用次数和运行时间。

关于注解的使用请参阅[注解章节](zh-cn/annotation)。

### 自定义 Histogram Bucket

> 本节只适用于 Prometheus 驱动

当您在使用 Prometheus 的 Histogram 时，有时会有自定义 Bucket 的需求。您可以在服务启动前，依赖注入 Registry 并自行注册 Histogram ，设置所需 Bucket 。稍后使用时 `MetricFactory` 就会调用您注册好同名 Histogram 。示例如下：

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
之后您使用 `$metricFactory->makeHistogram('test')` 时返回的就是您提前注册好的 Histogram 了。

### 自定义上报

> 本节只适用于 Prometheus 驱动

设置组件的 Promethues 驱动工作模式为自定义模式（ `Constants::CUSTOM_MODE` ）后，您可以自由的处理指标上报。在本节中，我们展示如何将指标存入 Redis 中，然后在 Worker 中添加一个新的 HTTP 路由，返回 Prometheus 渲染后的指标。

#### 使用 Redis 存储指标

指标的存储介质由 `Prometheus\Storage\Adapter` 接口定义。默认使用内存存储。我们可以在 `config/autoload/dependencies.php` 中更换为 Redis 存储。

```php
<?php

return [
    Prometheus\Storage\Adapter::class => Hyperf\Metric\Adapter\Prometheus\RedisStorageFactory::class,
];
```

#### 在 Worker 中添加 /metrics 路由

在 config/routes.php 中添加 Prometheus 路由。

> 注意若要在 Worker 下获取指标，需要您自行处理 Worker 之间状态共享问题。方法之一就是将状态按上文所述方式存储于 Redis 。

```php
<?php

use Hyperf\HttpServer\Router\Router;

Router::get('/metrics', function(){
    $registry = Hyperf\Context\ApplicationContext::getContainer()->get(Prometheus\CollectorRegistry::class);
    $renderer = new Prometheus\RenderTextFormat();
    return $renderer->render($registry->getMetricFamilySamples());
});
```

## 在 Grafana 创建控制台

> 本节只适用于 Prometheus 驱动

如果您启用了默认指标，`Hyperf/Metric` 为您准备了一个开箱即用的 Grafana 控制台。下载控制台 [json 文件](https://cdn.jsdelivr.net/gh/hyperf/hyperf/src/metric/grafana.json)，导入 Grafana 中即可使用。

![grafana](imgs/grafana.png)

## 注意事项

- 如需在 `hyperf/command` 自定义命令中使用本组件收集指标，需要在启动命令时添加命令行参数: `--enable-event-dispatcher`。

