# Trace

In microservice scenarios, a single business request may span 3-4 services at a minimum, and dozens or even more at a maximum. Debugging a problem in such an architecture is extremely difficult. We need a call chain tracing system to dynamically display the service call link, so that we can quickly locate the problem point, and also tune the service according to the link information.

In `Hyperf`, we provide the [hyperf/tracer](https://github.com/hyperf/tracer) component to track and analyze calls across network requests. Currently, it integrates with [Zipkin](https://zipkin.io/) and [Jaeger](https://www.jaegertracing.io/) systems based on the [OpenTracing](https://opentracing.io) protocol. Users can also customize implementations based on the OpenTracing protocol.

## Installation

### Install component via Composer

```bash
composer require hyperf/tracer
```

The [hyperf/tracer](https://github.com/hyperf/tracer) component installs [Zipkin](https://zipkin.io/) dependencies by default. If you want to use [Jaeger](https://www.jaegertracing.io/), you also need to execute the following command to install the corresponding dependencies:

```bash
composer require jonahgeorge/jaeger-client-php
```

### Add component configuration

If the file does not exist, you can execute the following command to add the `config/autoload/opentracing.php` configuration file:

```bash
php bin/hyperf.php vendor:publish hyperf/tracer
```

## Usage

### Configuration

#### Configure tracing switches

By default, monitoring or `AOP` aspect processing is provided for `Guzzle HTTP` calls, `Redis` calls, and `DB` calls to implement the propagation and tracing of the call chain. By default, these traces are not enabled. You need to enable tracing for certain remote calls by changing the switches in the `enable` item within the `config/autoload/opentracing.php` configuration file.

```php
<?php

return [
    'enable' => [
        // Enable or disable tracing for Guzzle HTTP calls
        'guzzle' => false,
        // Enable or disable tracing for Redis calls
        'redis' => false,
        // Enable or disable tracing for DB calls
        'db' => false,
    ],
];
```

Before starting to trace, we also need to choose the Tracer driver to be used and configure the Tracer.

#### Choose Tracer driver

The value corresponding to `default` in the configuration file is the name of the driver used. The specific configuration of the driver is defined under the `tracer` item, using the same driver as the `key`.

```php
<?php

return [
    // Select the default Tracer driver, the selected Tracer name corresponds to the key defined under tracers
    'default' => env('TRACER_DRIVER', 'staging_zipkin'),

    // Other configurations omitted here
    'enable' => [],

    'tracer' => [
        // Zipkin configuration
        'staging_zipkin' => [
            'driver' => \Hyperf\Tracer\Adapter\ZipkinTracerFactory::class,
        ],
        // Another Zipkin configuration
        'producton_zipkin' => [
            'driver' => \Hyperf\Tracer\Adapter\ZipkinTracerFactory::class,
        ],
        // Jaeger configuration
        'jaeger' => [
            'driver' => \Hyperf\Tracer\Adapter\JaegerTracerFactory::class,
        ],
    ]
];
```

Note, as shown in the example configuration, you can configure multiple Zipkin drivers or Jaeger drivers. Although they use the same underlying system, their specific configurations can be different. A common scenario is that we want 100% sampling in the staging environment, but 1% sampling in the production environment. You can configure two drivers and then choose different drivers based on the environment variable under the `default` item.

#### Configure Zipkin

When using Zipkin, add the specific configuration of Zipkin to the `tracer` item in the configuration file.

```php
<?php
use Zipkin\Samplers\BinarySampler;

return [
    // Select the default Tracer
    'default' => env('TRACER_DRIVER', 'zipkin'),

    // Demonstration does not expand the configuration within enable
    'enable' => [],

    'tracer' => [
        // Zipkin driver configuration
        'zipkin' => [
            // Current application configuration
            'app' => [
                'name' => env('APP_NAME', 'skeleton'),
                // If ipv4 and ipv6 are empty, the component will automatically detect them from Server
                'ipv4' => '127.0.0.1',
                'ipv6' => null,
                'port' => 9501,
            ],
            'driver' => \Hyperf\Tracer\Adapter\ZipkinTracerFactory::class,
            'options' => [
                // Zipkin service endpoint URL
                'endpoint_url' => env('ZIPKIN_ENDPOINT_URL', 'http://localhost:9411/api/v2/spans'),
                // Request timeout in seconds
                'timeout' => env('ZIPKIN_TIMEOUT', 1),
            ],
            // Sampler, defaults to tracing all requests
            'sampler' => BinarySampler::createAsAlwaysSample(),
        ],
    ],
];
```

#### Configure Jaeger

When using Jaeger, add the specific configuration of Jaeger to the `tracer` item in the configuration file.

```php
<?php
use Hyperf\Tracer\Adapter\JaegerTracerFactory;
use const Jaeger\SAMPLER_TYPE_CONST;

return [
    // Select the default Tracer
    'default' => env('TRACER_DRIVER', 'jaeger'),

    // Demonstration does not expand the configuration within enable
    'enable' => [],

    'tracer' => [
        // Jaeger driver configuration
        'jaeger' => [
            'driver' => JaegerTracerFactory::class,
            // Project name
            'name' => env('APP_NAME', 'skeleton'),
            'options' => [
                // Sampler, defaults to tracing all requests
                'sampler' => [
                    'type' => SAMPLER_TYPE_CONST,
                    'param' => true,
                ],
                // Reporting agent
                'local_agent' => [
                    'reporting_host' => env('JAEGER_REPORTING_HOST', 'localhost'),
                    'reporting_port' => env('JAEGER_REPORTING_PORT', 5775),
                ],
            ],
        ],
    ],
];
```

For more configuration about Jaeger, you can view it [[here](https://github.com/jonahgeorge/jaeger-client-php)].

#### Configure JsonRPC tracing switch

JsonRPC link tracing is not in the unified configuration and is currently a `Beta` version feature.

We just need to configure `aspects.php` and add the following `Aspect` to enable it.

> Tip: Do not forget to add the corresponding TraceMiddleware on the peer side.

```php
<?php

return [
    Hyperf\Tracer\Aspect\JsonRpcAspect::class,
];
```

#### Configure Coroutine tracing switch

Coroutine link tracing is not in the unified configuration and is an optional feature.

We just need to configure `aspects.php` and add the following `Aspect` to enable it.

```php
<?php

return [
    Hyperf\Tracer\Aspect\CoroutineAspect::class,
];
```

### Configure middleware or listener

After configuring the driver, to collect information, you also need to configure the middleware or request cycle event listener to enable the collection function.

- Add middleware

Open the `config/autoload/middlewares.php` file and enable the middleware in the `http` node.

```php
<?php

declare(strict_types=1);

return [
    'http' => [
        \Hyperf\Tracer\Middleware\TraceMiddleware::class,
    ],
];
```

- Or add listener

Open the `config/autoload/listeners.php` file and add the listener.

```php
<?php

declare(strict_types=1);

return [
    \Hyperf\Tracer\Listener\RequestTraceListener::class,
];
```

### Configure Span tag

For some Span Tag names that Hyperf automatically collects, you can change the corresponding name by changing the Span Tag configuration. You only need to add the `tags` configuration in the `config/autoload/opentracing.php` configuration file. Refer to the configuration below. If the configuration item exists, the value of the configuration item shall prevail. If the configuration item does not exist, the component's default value shall prevail.

```php
return [
    'tags' => [
        // HTTP Client (Guzzle)
        'http_client' => [
            'http.url' => 'http.url',
            'http.method' => 'http.method',
            'http.status_code' => 'http.status_code',
        ],
        // Redis Client
        'redis' => [
            'arguments' => 'arguments',
            'result' => 'result',
        ],
        // Database Client (hyperf/database)
        'db' => [
            'db.query' => 'db.query',
            'db.statement' => 'db.statement',
            'db.query_time' => 'db.query_time',
        ],
    ]
];
```

### Replace Sampler

The default sampler records the call chain for all requests, which has a certain impact on performance, especially memory usage. Therefore, we only need to trace the call chain when we want to. Then we need to replace the sampler. Replacing it is also very simple. Taking Zipkin as an example, just change the value corresponding to the configuration item `opentracing.zipkin.sampler` to your sampler object instance, as long as your sampler object implements the `Zipkin\Sampler` interface class.

### Access Alibaba Cloud Link Tracing Service

When we use Alibaba Cloud's link tracing service, because the peer end also supports the `Zipkin` protocol, we can directly modify the `endpoint_url` in the `config/autoload/opentracing.php` configuration file to the address of your corresponding Alibaba Cloud `region`. The specific address can be obtained in Alibaba Cloud's link tracing service. For more details, please refer to [Alibaba Cloud Link Tracing Service Help Document](https://help.aliyun.com/document_detail/100031.html?spm=a2c4g.11186623.6.547.68f974dcZlg4Mv).

### Use other Tracer drivers

You can also use any other Tracer driver that complies with the OpenTracing protocol. In the Driver item, fill in any class that implements `Hyperf\Tracer\Contract\NamedFactoryInterface`. This interface has only one make function, the parameter is the driver name, and it needs to return an instance that implements OpenTracing\Tracer.

## Reference

- [Opentracing](https://opentracing.io)
- [Zipkin](https://zipkin.io/)
- [Jaeger](https://www.jaegertracing.io/)
- [Dapper, A Large-Scale Distributed Systems Tracing System](https://bigbully.github.io/Dapper-translation/)
