# 调用链追踪

在微服务场景下，我们会拆分出来很多的服务，也就意味着一个业务请求，少则跨越 3-4 个服务，多则几十个甚至更多，在这种架构下我们需要对某一个问题进行 Debug 的时候是极其困难的一件事情，那么我们就需要一个调用链追踪系统来帮助我们动态地展示服务调用的链路，以便我们可以快速地对问题点进行定位，亦可根据链路信息对服务进行调优。   
在 `Hyperf` 里我们提供了 [hyperf/tracer](https://github.com/hyperf/tracer) 组件来对各个跨网络请求来进行调用的追踪以及分析，目前根据 [OpenTracing](https://opentracing.io) 协议对接了 [Zipkin](https://zipkin.io/) 系统和 [Jaeger](https://www.jaegertracing.io/) 系统，用户也可以根据 OpenTracing 协议定制实现。

## 安装

### 通过 Composer 安装组件

```bash
composer require hyperf/tracer
```

[hyperf/tracer](https://github.com/hyperf/tracer) 组件默认安装了 [Zipkin](https://zipkin.io/) 相关依赖。如果要使用 [Jaeger](https://www.jaegertracing.io/)，还需要执行下面的命令安装对应的依赖：

```bash
composer require jonahgeorge/jaeger-client-php
```

### 增加组件配置

如文件不存在，可执行下面的命令增加 `config/autoload/opentracing.php` 配置文件：

```bash
php bin/hyperf.php vendor:publish hyperf/tracer
```

## 使用

### 配置

#### 配置追踪开关

默认提供了对 `Guzzle HTTP` 调用、`Redis` 调用、`DB` 调用进行了监听或 `AOP` 切面处理，以实现对调用链的传播与追踪，默认情况下这些追踪不会打开，您需要通过更改 `config/autoload/opentracing.php` 配置文件内的 `enable` 项内的开关来打开对某些远程调用的追踪。

```php
<?php

return [
    'enable' => [
        // 打开或关闭对 Guzzle HTTP 调用的追踪
        'guzzle' => false,
        // 打开或关闭对 Redis 调用的追踪
        'redis' => false,
        // 打开或关闭对 DB  调用的追踪
        'db' => false,
    ],
];
```

在开始追踪之前，我们还需要选择所使用的 Tracer 驱动，并对 Tracer 进行配置。

#### 选择追踪器驱动

配置文件内的 `default` 对应的值则为使用的驱动名称。驱动的具体配置在 `tracer` 项下定义，使用与 `key` 相同的驱动。

```php
<?php

return [
    // 选择默认的 Tracer 驱动，所选 Tracer 名称对应 tracers 下定义的键
    'default' => env('TRACER_DRIVER', 'staging_zipkin'),

    // 这里暂时省略其他配置
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

注意，如示例配置所示，您可以配置多套 Zipkin 驱动或 Jaeger 驱动。虽然采用的底层系统一样，但他们的具体配置可以不同。一种常见场景是我们希望测试环境 100% 采样，但在生产环境下 1% 采样，就可以配置两套驱动，然后根据 `default` 项下的环境变量来选择不同的驱动。

#### 配置 Zipkin

使用 Zipkin 时，在配置文件中的 `tracer` 项增加 Zipkin 的具体配置。

```php
<?php
use Zipkin\Samplers\BinarySampler;

return [
    // 选择默认的 Tracer
    'default' => env('TRACER_DRIVER', 'zipkin'),

    // 这里的代码演示不对 enable 内的配置进行展开
    'enable' => [],

    'tracer' => [
        // Zipkin 驱动配置
        'zipkin' => [
            // 当前应用的配置
            'app' => [
                'name' => env('APP_NAME', 'skeleton'),
                // 如果 ipv6 和 ipv6 为空组件会自动从 Server 中检测
                'ipv4' => '127.0.0.1',
                'ipv6' => null,
                'port' => 9501,
            ],
            'driver' => \Hyperf\Tracer\Adapter\ZipkinTracerFactory::class,
            'options' => [
                // Zipkin 服务的 endpoint 地址
                'endpoint_url' => env('ZIPKIN_ENDPOINT_URL', 'http://localhost:9411/api/v2/spans'),
                // 请求超时秒数
                'timeout' => env('ZIPKIN_TIMEOUT', 1),
            ],
            // 采样器，默认为所有请求的都追踪
            'sampler' => BinarySampler::createAsAlwaysSample(),
        ],
    ],
];
```

#### 配置 Jaeger

使用 Jaeger 时，在配置文件中的 `tracer` 项增加 Jaeger 的具体配置。

```php
<?php
use Hyperf\Tracer\Adapter\JaegerTracerFactory;
use const Jaeger\SAMPLER_TYPE_CONST;

return [
    // 选择默认的 Tracer
    'default' => env('TRACER_DRIVER', 'jaeger'),

    // 这里的代码演示不对 enable 内的配置进行展开
    'enable' => [],

    'tracer' => [
        // Jaeger 驱动配置
        'jaeger' => [
            'driver' => JaegerTracerFactory::class,
            // 项目名称
            'name' => env('APP_NAME', 'skeleton'),
            'options' => [
                // 采样器，默认为所有请求的都追踪
                'sampler' => [
                    'type' => SAMPLER_TYPE_CONST,
                    'param' => true,
                ],
                // 上报地址
                'local_agent' => [
                    'reporting_host' => env('JAEGER_REPORTING_HOST', 'localhost'),
                    'reporting_port' => env('JAEGER_REPORTING_PORT', 5775),
                ],
            ],
        ],
    ],
];
```

关于 Jaeger 的更多配置可以在 [[这里](https://github.com/jonahgeorge/jaeger-client-php)] 查看。

#### 配置 JsonRPC 追踪开关

JsonRPC 的链路追踪并不在统一配置当中，暂时还属于 `Beta` 版本的功能。

我们只需要配置 `aspects.php`，加入以下 `Aspect` 即可开启。

> 提示：不要忘了在对端，添加对应的 TraceMiddleware

```php
<?php

return [
    Hyperf\Tracer\Aspect\JsonRpcAspect::class,
];
```

#### 配置协程追踪开关

协程的链路追踪并不在统一配置当中，属于可选版本的功能。

我们只需要配置 `aspects.php`，加入以下 `Aspect` 即可开启。

```php
<?php

return [
    Hyperf\Tracer\Aspect\CoroutineAspect::class,
];
```

### 配置中间件或监听器

配置完驱动之后，采集信息还需要配置一下中间件或请求周期事件监听器才能启用采集功能。

- 添加中间件

打开 `config/autoload/middlewares.php` 文件，在 `http` 节点启用中间件。

```php
<?php

declare(strict_types=1);

return [
    'http' => [
        \Hyperf\Tracer\Middleware\TraceMiddleware::class,
    ],
];
```

- 或者添加监听器

打开 `config/autoload/listeners.php` 文件，添加监听器。

```php
<?php

declare(strict_types=1);

return [
    \Hyperf\Tracer\Listener\RequestTraceListener::class,
];
```

### 配置 Span tag

对于一些 Hyperf 自动收集追踪信息的 Span Tag 名称，可以通过更改 Span Tag 配置来更改对应的名称，只需在配置文件 `config/autolaod/opentracing.php` 内增加 `tags` 配置即可，参考配置如下。如配置项存在，则以配置项的值为准，如配置项不存在，则以组件的默认值为准。

```php
return [
    'tags' => [
        // HTTP 客户端 (Guzzle)
        'http_client' => [
            'http.url' => 'http.url',
            'http.method' => 'http.method',
            'http.status_code' => 'http.status_code',
        ],
        // Redis 客户端
        'redis' => [
            'arguments' => 'arguments',
            'result' => 'result',
        ],
        // 数据库客户端 (hyperf/database)
        'db' => [
            'db.query' => 'db.query',
            'db.statement' => 'db.statement',
            'db.query_time' => 'db.query_time',
        ],
    ]
];
```

### 更换采样器

默认的采样器为所有请求都记录调用链，这对性能会存在一定程度上的影响，尤其是内存的占用，所以我们只需要在我们希望的时候才对调用链进行追踪，那么我们就需要对采样器进行更换，更换也很简单，以 Zipkin 为例，只需对配置项 `opentracing.zipkin.sampler` 对应的值改为您的采样器对象实例即可，只要您的采样器对象实现了 `Zipkin\Sampler` 接口类即可。

### 接入阿里云链路追踪服务

当我们在使用阿里云的链路追踪服务时，由于对端也是支持 `Zipkin` 的协议的，故可以直接通过在 `config/autoload/opentracing.php` 配置文件内修改 `endpoint_url` 的值为您对应的阿里云 `region` 的地址，具体地址可在阿里云的链路追踪服务内得到，更多细节可参考 [阿里云链路追踪服务帮助文档](https://help.aliyun.com/document_detail/100031.html?spm=a2c4g.11186623.6.547.68f974dcZlg4Mv)。

### 使用其他 Tracer 驱动

您也可以使用其他任意符合 OpenTracing 协议的 Tracer 驱动。在 Driver 项中，填写任意实现了 `Hyperf\Tracer\Contract\NamedFactoryInterface` 的类就可以了。该接口只有一个 make 函数，参数为驱动名称，需返回一个实现了 OpenTracing\Tracer 的实例。

## Reference

- [Opentracing](https://opentracing.io)
- [Zipkin](https://zipkin.io/)
- [Jaeger](https://www.jaegertracing.io/)
- [Dapper, 大规模分布式系统的跟踪系统](https://bigbully.github.io/Dapper-translation/)
