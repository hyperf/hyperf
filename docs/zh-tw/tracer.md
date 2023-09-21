# 呼叫鏈追蹤

在微服務場景下，我們會拆分出來很多的服務，也就意味著一個業務請求，少則跨越 3-4 個服務，多則幾十個甚至更多，在這種架構下我們需要對某一個問題進行 Debug 的時候是極其困難的一件事情，那麼我們就需要一個呼叫鏈追蹤系統來幫助我們動態地展示服務呼叫的鏈路，以便我們可以快速地對問題點進行定位，亦可根據鏈路資訊對服務進行調優。   
在 `Hyperf` 裡我們提供了 [hyperf/tracer](https://github.com/hyperf/tracer) 元件來對各個跨網路請求來進行呼叫的追蹤以及分析，目前根據 [OpenTracing](https://opentracing.io) 協議對接了 [Zipkin](https://zipkin.io/) 系統和 [Jaeger](https://www.jaegertracing.io/) 系統，使用者也可以根據 OpenTracing 協議定製實現。

## 安裝

### 透過 Composer 安裝元件

```bash
composer require hyperf/tracer
```

[hyperf/tracer](https://github.com/hyperf/tracer) 元件預設安裝了 [Zipkin](https://zipkin.io/) 相關依賴。如果要使用 [Jaeger](https://www.jaegertracing.io/)，還需要執行下面的命令安裝對應的依賴：

```bash
composer require jonahgeorge/jaeger-client-php
```

### 增加元件配置

如檔案不存在，可執行下面的命令增加 `config/autoload/opentracing.php` 配置檔案：

```bash
php bin/hyperf.php vendor:publish hyperf/tracer
```

## 使用

### 配置

#### 配置追蹤開關

預設提供了對 `Guzzle HTTP` 呼叫、`Redis` 呼叫、`DB` 呼叫進行了監聽或 `AOP` 切面處理，以實現對呼叫鏈的傳播與追蹤，預設情況下這些追蹤不會開啟，您需要透過更改 `config/autoload/opentracing.php` 配置檔案內的 `enable` 項內的開關來開啟對某些遠端呼叫的追蹤。

```php
<?php

return [
    'enable' => [
        // 開啟或關閉對 Guzzle HTTP 呼叫的追蹤
        'guzzle' => false,
        // 開啟或關閉對 Redis 呼叫的追蹤
        'redis' => false,
        // 開啟或關閉對 DB  呼叫的追蹤
        'db' => false,
    ],
];
```

在開始追蹤之前，我們還需要選擇所使用的 Tracer 驅動，並對 Tracer 進行配置。

#### 選擇追蹤器驅動

配置檔案內的 `default` 對應的值則為使用的驅動名稱。驅動的具體配置在 `tracer` 項下定義，使用與 `key` 相同的驅動。

```php
<?php

return [
    // 選擇預設的 Tracer 驅動，所選 Tracer 名稱對應 tracers 下定義的鍵
    'default' => env('TRACER_DRIVER', 'staging_zipkin'),

    // 這裡暫時省略其他配置
    'enable' => [],

    'tracer' => [
        // Zipkin 配置
        'staging_zipkin' => [
            'driver' => \Hyperf\Tracer\Adapter\ZipkinTracerFactory::class,
        ],
        // 另一套 Zipkin 配置
        'producton_zipkin' => [
            'driver' => \Hyperf\Tracer\Adapter\ZipkinTracerFactory::class,
        ],
        // Jaeger 配置
        'jaeger' => [
            'driver' => \Hyperf\Tracer\Adapter\JaegerTracerFactory::class,
        ],
    ]
];
```

注意，如示例配置所示，您可以配置多套 Zipkin 驅動或 Jaeger 驅動。雖然採用的底層系統一樣，但他們的具體配置可以不同。一種常見場景是我們希望測試環境 100% 取樣，但在生產環境下 1% 取樣，就可以配置兩套驅動，然後根據 `default` 項下的環境變數來選擇不同的驅動。

#### 配置 Zipkin

使用 Zipkin 時，在配置檔案中的 `tracer` 項增加 Zipkin 的具體配置。

```php
<?php
use Zipkin\Samplers\BinarySampler;

return [
    // 選擇預設的 Tracer
    'default' => env('TRACER_DRIVER', 'zipkin'),

    // 這裡的程式碼演示不對 enable 內的配置進行展開
    'enable' => [],

    'tracer' => [
        // Zipkin 驅動配置
        'zipkin' => [
            // 當前應用的配置
            'app' => [
                'name' => env('APP_NAME', 'skeleton'),
                // 如果 ipv6 和 ipv6 為空元件會自動從 Server 中檢測
                'ipv4' => '127.0.0.1',
                'ipv6' => null,
                'port' => 9501,
            ],
            'driver' => \Hyperf\Tracer\Adapter\ZipkinTracerFactory::class,
            'options' => [
                // Zipkin 服務的 endpoint 地址
                'endpoint_url' => env('ZIPKIN_ENDPOINT_URL', 'http://localhost:9411/api/v2/spans'),
                // 請求超時秒數
                'timeout' => env('ZIPKIN_TIMEOUT', 1),
            ],
            // 取樣器，預設為所有請求的都追蹤
            'sampler' => BinarySampler::createAsAlwaysSample(),
        ],
    ],
];
```

#### 配置 Jaeger

使用 Jaeger 時，在配置檔案中的 `tracer` 項增加 Jaeger 的具體配置。

```php
<?php
use Hyperf\Tracer\Adapter\JaegerTracerFactory;
use const Jaeger\SAMPLER_TYPE_CONST;

return [
    // 選擇預設的 Tracer
    'default' => env('TRACER_DRIVER', 'jaeger'),

    // 這裡的程式碼演示不對 enable 內的配置進行展開
    'enable' => [],

    'tracer' => [
        // Jaeger 驅動配置
        'jaeger' => [
            'driver' => JaegerTracerFactory::class,
            // 專案名稱
            'name' => env('APP_NAME', 'skeleton'),
            'options' => [
                // 取樣器，預設為所有請求的都追蹤
                'sampler' => [
                    'type' => SAMPLER_TYPE_CONST,
                    'param' => true,
                ],
                // 上報地址
                'local_agent' => [
                    'reporting_host' => env('JAEGER_REPORTING_HOST', 'localhost'),
                    'reporting_port' => env('JAEGER_REPORTING_PORT', 5775),
                ],
            ],
        ],
    ],
];
```

關於 Jaeger 的更多配置可以在 [[這裡](https://github.com/jonahgeorge/jaeger-client-php)] 檢視。

#### 配置 JsonRPC 追蹤開關

JsonRPC 的鏈路追蹤並不在統一配置當中，暫時還屬於 `Beta` 版本的功能。

我們只需要配置 `aspects.php`，加入以下 `Aspect` 即可開啟。

> 提示：不要忘了在對端，新增對應的 TraceMiddleware

```php
<?php

return [
    Hyperf\Tracer\Aspect\JsonRpcAspect::class,
];
```

#### 配置協程追蹤開關

協程的鏈路追蹤並不在統一配置當中，屬於可選版本的功能。

我們只需要配置 `aspects.php`，加入以下 `Aspect` 即可開啟。

```php
<?php

return [
    Hyperf\Tracer\Aspect\CoroutineAspect::class,
];
```

### 配置中介軟體或監聽器

配置完驅動之後，採集資訊還需要配置一下中介軟體或請求週期事件監聽器才能啟用採集功能。

- 新增中介軟體

開啟 `config/autoload/middlewares.php` 檔案，在 `http` 節點啟用中介軟體。

```php
<?php

declare(strict_types=1);

return [
    'http' => [
        \Hyperf\Tracer\Middleware\TraceMiddleware::class,
    ],
];
```

- 或者新增監聽器

開啟 `config/autoload/listeners.php` 檔案，新增監聽器。

```php
<?php

declare(strict_types=1);

return [
    \Hyperf\Tracer\Listener\RequestTraceListener::class,
];
```

### 配置 Span tag

對於一些 Hyperf 自動收集追蹤資訊的 Span Tag 名稱，可以透過更改 Span Tag 配置來更改對應的名稱，只需在配置檔案 `config/autolaod/opentracing.php` 內增加 `tags` 配置即可，參考配置如下。如配置項存在，則以配置項的值為準，如配置項不存在，則以元件的預設值為準。

```php
return [
    'tags' => [
        // HTTP 客戶端 (Guzzle)
        'http_client' => [
            'http.url' => 'http.url',
            'http.method' => 'http.method',
            'http.status_code' => 'http.status_code',
        ],
        // Redis 客戶端
        'redis' => [
            'arguments' => 'arguments',
            'result' => 'result',
        ],
        // 資料庫客戶端 (hyperf/database)
        'db' => [
            'db.query' => 'db.query',
            'db.statement' => 'db.statement',
            'db.query_time' => 'db.query_time',
        ],
    ]
];
```

### 更換取樣器

預設的取樣器為所有請求都記錄呼叫鏈，這對效能會存在一定程度上的影響，尤其是記憶體的佔用，所以我們只需要在我們希望的時候才對呼叫鏈進行追蹤，那麼我們就需要對取樣器進行更換，更換也很簡單，以 Zipkin 為例，只需對配置項 `opentracing.zipkin.sampler` 對應的值改為您的取樣器物件例項即可，只要您的取樣器物件實現了 `Zipkin\Sampler` 介面類即可。

### 接入阿里雲鏈路追蹤服務

當我們在使用阿里雲的鏈路追蹤服務時，由於對端也是支援 `Zipkin` 的協議的，故可以直接透過在 `config/autoload/opentracing.php` 配置檔案內修改 `endpoint_url` 的值為您對應的阿里雲 `region` 的地址，具體地址可在阿里雲的鏈路追蹤服務內得到，更多細節可參考 [阿里雲鏈路追蹤服務幫助文件](https://help.aliyun.com/document_detail/100031.html?spm=a2c4g.11186623.6.547.68f974dcZlg4Mv)。

### 使用其他 Tracer 驅動

您也可以使用其他任意符合 OpenTracing 協議的 Tracer 驅動。在 Driver 項中，填寫任意實現了 `Hyperf\Tracer\Contract\NamedFactoryInterface` 的類就可以了。該介面只有一個 make 函式，引數為驅動名稱，需返回一個實現了 OpenTracing\Tracer 的例項。

## Reference

- [Opentracing](https://opentracing.io)
- [Zipkin](https://zipkin.io/)
- [Jaeger](https://www.jaegertracing.io/)
- [Dapper, 大規模分散式系統的跟蹤系統](https://bigbully.github.io/Dapper-translation/)
