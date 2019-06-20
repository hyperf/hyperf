# 调用链追踪

在微服务场景下，我们会拆分出来很多的服务，也就意味着一个业务请求，少则跨越 3-4 个服务，多则几十个甚至更多，在这种架构下我们需要对某一个问题进行 Debug 的时候是极其困难的一件事情，那么我们就需要一个调用链追踪系统来帮助我们动态地展示服务调用的链路，以便我们可以快速地对问题点进行定位，亦可根据链路信息对服务进行调优。   
在 `Hyperf` 里我们提供了 [hyperf/tracer](https://github.com/hyperf-cloud/tracer) 组件来对各个跨网络请求来进行调用的追踪以及分析，目前仅根据 [Opentracing](https://opentracing.io) 协议对接了 [Zipkin](https://zipkin.io/) 系统。

# 安装

## 通过 Composer 安装组件

```bash
composer require hyperf/tracer
```

## 增加组件配置

如文件不存在，可执行下面的命令增加 `config/autoload/opentracing.php` 配置文件：

```bash
php bin/hyperf.php vendor:publish
```

# 使用

## 配置

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

在开始追踪之前我们还需要配置一下 `zipkin` 的服务配置，还是在同一个文件下

```php
<?php
use Zipkin\Samplers\BinarySampler;

return [
    // 这里的代码演示不对 enable 内的配置进行展开
    'enable' => [],
    // zipkin 服务配置
    'zipkin' => [
        // 当前应用的配置
        'app' => [
            'name' => env('APP_NAME', 'skeleton'),
            // 如果 ipv6 和 ipv6 为空组件会自动从 Server 中检测
            'ipv4' => '127.0.0.1',
            'ipv6' => null,
            'port' => 9501,
        ],
        'options' => [
            // zipkin 服务的 endpoint 地址
            'endpoint_url' => env('ZIPKIN_ENDPOINT_URL', 'http://localhost:9411/api/v2/spans'),
            // 请求超时秒数
            'timeout' => env('ZIPKIN_TIMEOUT', 1),
        ],
        // 采样器，默认为所有请求的都追踪
        'sampler' => BinarySampler::createAsAlwaysSample(),
    ],
];
```

## 更换采样器

默认的采样器为所有请求都记录调用链，这对性能会存在一定程度上的影响，尤其是内存的占用，所以我们只需要在我们希望的时候才对调用链进行追踪，那么我们就需要对采样器进行更换，更换也很简单，只需对配置项 `opentracing.zipkin.sampler` 对应的值改为您的采样器对象实例即可，只要您的采样器对象实现了 `Zipkin\Sampler` 接口类即可。

## 接入阿里云链路追踪服务

当我们在使用阿里云的链路追踪服务时，由于对端也是支持 `Zipkin` 的协议的，故可以直接通过在 `condif/autoload/opentracing.php` 配置文件内修改 `endpoint_url` 的值为您对应的阿里云 `region` 的地址，具体地址可在阿里云的链路追踪服务内得到，更多细节可参考 [阿里云链路追踪服务帮助文档](https://help.aliyun.com/document_detail/100031.html?spm=a2c4g.11186623.6.547.68f974dcZlg4Mv)。

# Reference
- [Opentracing](https://opentracing.io)
- [Zipkin](https://zipkin.io/)
- [Dapper, 大规模分布式系统的跟踪系统](https://bigbully.github.io/Dapper-translation/)
