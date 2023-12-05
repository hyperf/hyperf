# 服務監控

微服務治理的一個核心需求便是服務可觀察性。作為微服務的牧羊人，要做到時刻掌握各項服務的健康狀態，並非易事。雲原生時代這一領域內湧現出了諸多解決方案。本元件對可觀察性當中的重要支柱遙測與監控進行了抽象，方便使用者與既有基礎設施快速結合，同時避免供應商鎖定。

## 安裝

### 透過 Composer 安裝元件

```bash
composer require hyperf/metric
```

Metric 支援 [Prometheus](https://prometheus.io/)、[StatsD](https://github.com/statsd/statsd) 和 [InfluxDB](http://influxdb.com)，可以執行下面的命令安裝對應的依賴：

```bash
# Prometheus
composer require promphp/prometheus_client_php
# StatsD 所需依賴
composer require domnikl/statsd
# InfluxDB 所需依賴 
composer require influxdb/influxdb-php 
```

### 增加元件配置

如檔案不存在，可執行下面的命令增加 `config/autoload/metric.php` 配置檔案：

```bash
php bin/hyperf.php vendor:publish hyperf/metric
```

## 使用

### 配置

#### 選項

`default`：配置檔案內的 `default` 對應的值則為使用的驅動名稱。驅動的具體配置在 `metric` 項下定義，使用與 `key` 相同的驅動。

```php
'default' => env('METRIC_DRIVER', 'prometheus'),
```

* `use_standalone_process`: 是否使用 `獨立監控程序`。推薦開啟。關閉後將在 `Worker 程序` 中處理指標收集與上報。

```php
'use_standalone_process' => env('TELEMETRY_USE_STANDALONE_PROCESS', true),
```

* `enable_default_metric`: 是否統計預設指標。預設指標包括記憶體佔用、系統 CPU 負載以及 Swoole Server 指標和 Swoole Coroutine 指標。

```php
'enable_default_metric' => env('TELEMETRY_ENABLE_DEFAULT_TELEMETRY', true),
```

`default_metric_interval`: 預設指標推送週期，單位為秒，下同。
```php
'default_metric_interval' => env('DEFAULT_METRIC_INTERVAL', 5),
```
#### 配置 Prometheus

使用 Prometheus 時，在配置檔案中的 `metric` 項增加 Prometheus 的具體配置。

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

Prometheus 有兩種工作模式，爬模式與推模式（透過 Prometheus Pushgateway ），本元件均可支援。

使用爬模式（Prometheus 官方推薦）時需設定：

```php
'mode' => Constants::SCRAPE_MODE
```

並配置爬取地址 `scrape_host`、爬取埠 `scrape_port`、爬取路徑 `scrape_path`。Prometheus 可以在對應配置下以 HTTP 訪問形式拉取全部指標。

> 注意：非同步風格下，爬模式必須啟用獨立程序，即 `use_standalone_process = true`。

使用推模式時需設定：

```php
'mode' => Constants::PUSH_MODE
```

並配置推送地址 `push_host`、推送埠 `push_port`、推送間隔 `push_interval`。只建議離線任務使用推模式。

因為基礎設定的差異性，可能以上模式都無法滿足需求。本元件還支援自定義模式。在自定義模式下，元件只負責指標的收集，具體的上報需要使用者自行處理。

```php
'mode' => Constants::CUSTOM_MODE
```
例如，您可能希望透過自定義的路由上報指標，或希望將指標存入 Redis 中，由其他獨立服務負責指標的集中上報等。[自定義上報](#自定義上報)一節包含了相應的示例。

#### 配置 StatsD

使用 StatsD 時，在配置檔案中的 `metric` 項增加 StatsD 的具體配置。

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

StatsD 目前只支援 UDP 模式，需要配置 UDP 地址 `udp_host`，UDP 埠 `udp_port`、是否批次推送 `enable_batch`（減少請求次數）、批次推送間隔 `push_interval` 以及取樣率 `sample_rate` 。

#### 配置 InfluxDB

使用 InfluxDB 時，在配置檔案中的 `metric` 項增加 InfluxDB 的具體配置。

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

InfluxDB 使用預設的 HTTP 模式，需要配置地址 `host`，UDP 埠 `port`、使用者名稱  `username`、密碼 `password`、`dbname` 資料表以及批次推送間隔 `push_interval`。

### 基本抽象

遙測元件對常用的三種資料型別進行了抽象，以確保解耦具體實現。

三種類型分別為：

計數器(Counter): 用於描述單向遞增的某種指標。如 HTTP 請求計數。

```php
interface CounterInterface
{
    public function with(string ...$labelValues): self;

    public function add(int $delta);
}
```

測量器(Gauge)：用於描述某種隨時間發生增減變化的指標。如連線池內的可用連線數。

```php
interface GaugeInterface
{
    public function with(string ...$labelValues): self;

    public function set(float $value);

    public function add(float $delta);
}
```

* 直方圖(Histogram)：用於描述對某一事件的持續觀測後產生的統計學分佈，通常表示為百分位數或分桶。如 HTTP 請求延遲。

```php
interface HistogramInterface
{
    public function with(string ...$labelValues): self;

    public function put(float $sample);
}
```

### 配置中介軟體

配置完驅動之後，只需配置一下中介軟體就能啟用請求 Histogram 統計功能。
開啟 `config/autoload/middlewares.php` 檔案，示例為在 `http` Server 中啟用中介軟體。

```php
<?php

declare(strict_types=1);

return [
    'http' => [
        \Hyperf\Metric\Middleware\MetricMiddleware::class,
    ],
];
```
> 本中介軟體中統計維度包含 `request_status`、`request_path`、`request_method`。如果您的 `request_path` 過多，則建議重寫本中介軟體，去掉 `request_path` 維度，否則過高的基數會導致記憶體溢位。

### 自定義使用

透過 HTTP 中介軟體遙測僅僅是本元件用途的冰山一角，您可以注入 `Hyperf\Metric\Contract\MetricFactoryInterface` 類來自行遙測業務資料。比如：建立的訂單數量、廣告的點選數量等。

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Order;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Metric\Contract\MetricFactoryInterface;

class IndexController extends AbstractController
{
    /**
     * @var MetricFactoryInterface
     */
    #[Inject]
    private $metricFactory;

    public function create(Order $order)
    {
        $counter = $this->metricFactory->makeCounter('order_created', ['order_type']);
        $counter->with($order->type)->add(1);
        // 訂單邏輯...
    }

}
```

`MetricFactoryInterface` 中包含如下工廠方法來生成對應的三種基本統計型別。

```php
public function makeCounter($name, $labelNames): CounterInterface;

public function makeGauge($name, $labelNames): GaugeInterface;

public function makeHistogram($name, $labelNames): HistogramInterface;
```

上述例子是統計請求範圍內的產生的指標。有時候我們需要統計的指標是面向完整生命週期的，比如統計非同步佇列長度或庫存商品數量。此種場景下可以監聽 `MetricFactoryReady` 事件。

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

> 工程上講，直接從 Redis 查詢佇列長度不太合適，應該透過佇列驅動 `DriverInterface` 介面下的 `info()` 方法來獲取佇列長度。這裡只做簡易演示。您可以在本元件原始碼的`src/Listener` 資料夾下找到完整例子。

### 註解

您可以使用 `#[Counter(name="stat_name_here")]` 和 `#[Histogram(name="stat_name_here")]` 來統計切面的呼叫次數和執行時間。

關於註解的使用請參閱[註解章節](zh-tw/annotation)。

### 自定義 Histogram Bucket

> 本節只適用於 Prometheus 驅動

當您在使用 Prometheus 的 Histogram 時，有時會有自定義 Bucket 的需求。您可以在服務啟動前，依賴注入 Registry 並自行註冊 Histogram ，設定所需 Bucket 。稍後使用時 `MetricFactory` 就會呼叫您註冊好同名 Histogram 。示例如下：

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
之後您使用 `$metricFactory->makeHistogram('test')` 時返回的就是您提前註冊好的 Histogram 了。

### 自定義上報

> 本節只適用於 Prometheus 驅動

設定元件的 Promethues 驅動工作模式為自定義模式（ `Constants::CUSTOM_MODE` ）後，您可以自由的處理指標上報。在本節中，我們展示如何將指標存入 Redis 中，然後在 Worker 中新增一個新的 HTTP 路由，返回 Prometheus 渲染後的指標。

#### 使用 Redis 儲存指標

指標的儲存介質由 `Prometheus\Storage\Adapter` 介面定義。預設使用記憶體儲存。我們可以在 `config/autoload/dependencies.php` 中更換為 Redis 儲存。

```php
<?php

return [
    Prometheus\Storage\Adapter::class => Hyperf\Metric\Adapter\Prometheus\RedisStorageFactory::class,
];
```

#### 在 Worker 中新增 /metrics 路由

在 config/routes.php 中新增 Prometheus 路由。

> 注意若要在 Worker 下獲取指標，需要您自行處理 Worker 之間狀態共享問題。方法之一就是將狀態按上文所述方式儲存於 Redis 。

```php
<?php

use Hyperf\HttpServer\Router\Router;

Router::get('/metrics', function(){
    $registry = Hyperf\Context\ApplicationContext::getContainer()->get(Prometheus\CollectorRegistry::class);
    $renderer = new Prometheus\RenderTextFormat();
    return $renderer->render($registry->getMetricFamilySamples());
});
```

## 在 Grafana 建立控制檯

> 本節只適用於 Prometheus 驅動

如果您啟用了預設指標，`Hyperf/Metric` 為您準備了一個開箱即用的 Grafana 控制檯。下載控制檯 [json 檔案](https://cdn.jsdelivr.net/gh/hyperf/hyperf/src/metric/grafana.json)，匯入 Grafana 中即可使用。

![grafana](imgs/grafana.png)

## 注意事項

- 如需在 `hyperf/command` 自定義命令中使用本元件收集指標，需要在啟動命令時新增命令列引數: `--enable-event-dispatcher`。

