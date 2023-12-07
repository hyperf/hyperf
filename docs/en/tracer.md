# Call link tracking

In the microservice architecture, there will be a lot of services by splitting, which means that a business request may go through 3 or 4 services at least, even dozens or more. Under this architecture, it is extremely difficult when we need to debug a certain problem. Then we need a call link tracking system to help us dynamically display the service call link so that we can quickly locate the problem, and also optimize the service based on the link information.
In `Hyperf`, we provide the [hyperf/tracer](https://github.com/hyperf/tracer) component to track and analyze the call of each cross-network request. Currently, the [Zipkin](https://zipkin.io/) system and the [Jaeger](https://www.jaegertracing.io/) system are docked according to the [OpenTracing](https://opentracing.io) protocol. Users can also customize this by following the OpenTracing protocol.

## Installation

### By Composer

```bash
composer require hyperf/tracer
```

The [hyperf/tracer](https://github.com/hyperf/tracer) component has installed [Zipkin](https://zipkin.io/) related dependencies by default. If you want to use [Jaeger](https://www.jaegertracing.io/), you need to execute the following command to install the corresponding dependencies:

```bash
composer require jonahgeorge/jaeger-client-php
```

### Add component configuration

If the file does not exist, execute the following command to add the `config/autoload/opentracing.php` configuration file:

```bash
php bin/hyperf.php vendor:publish hyperf/tracer
```

## usage

### Config

#### Enable tracking

By default, it provides monitoring of `Guzzle HTTP` calls, `Redis` calls, and `DB` calls or `AOP` aspect processing to achieve the propagation and tracking of the call link. These tracings are not enabled by default. You need to modify `enable` items in the `config/autoload/opentracing.php` configuration file to enable the tracing of certain remote calls.

```php
<?php

return [
    'enable' => [
        // enable the tracing of Guzzle HTTP calls
        'guzzle' => false,
        // enable the tracing of Redis calls
        'redis' => false,
        // enable the tracing of DB calls
        'db' => false,
    ],
];
```

Before starting to trace, we need to select the Tracer driver to be used and configure the Tracer.

#### Select tracker driver

The value corresponding to `default` in the configuration file is the name of the used driver. The specific configuration of the driver is defined under the `tracer` item, using the same driver as the `key`.

```php
<?php

return [
    // Select the default Tracer driver, the selected Tracer name corresponds to the key defined under tracers
    'default' => env('TRACER_DRIVER', 'staging_zipkin'),

    // Other configurations are omitted here in this example
    'enable' => [],

    'tracer' => [
        // Zipkin config
        'staging_zipkin' => [
            'driver' => \Hyperf\Tracer\Adapter\ZipkinTracerFactory::class,
        ],
        // another Zipkin config
        'producton_zipkin' => [
            'driver' => \Hyperf\Tracer\Adapter\ZipkinTracerFactory::class,
        ],
        // Jaeger config
        'jaeger' => [
            'driver' => \Hyperf\Tracer\Adapter\JaegerTracerFactory::class,
        ],
    ]
];
```

Note that as shown in the example configuration, you can configure multiple sets of Zipkin drivers or Jaeger drivers. Although the underlying systems used are the same, their specific configurations can be different. A common scenario is that we want 100% sampling rate in the test environment, but with 1% sampling rate in the production environment, two sets of drivers can be configured, and then different drivers can be selected according to the environment variables under the `default` item.

#### Configure Zipkin

When using Zipkin, add the specific configuration of Zipkin to the `tracer` item in the configuration file.

```php
<?php
use Zipkin\Samplers\BinarySampler;

return [
    // default Tracer
    'default' => env('TRACER_DRIVER', 'zipkin'),

    // Other configurations are omitted here in this example
    'enable' => [],

    'tracer' => [
        // Zipkin drive config
        'zipkin' => [
            // current app config
            'app' => [
                'name' => env('APP_NAME', 'skeleton'),
                // If ipv6 and ipv6 are null, the component will automatically detect from the Server
                'ipv4' => '127.0.0.1',
                'ipv6' => null,
                'port' => 9501,
            ],
            'driver' => \Hyperf\Tracer\Adapter\ZipkinTracerFactory::class,
            'options' => [
                // the endpoint address of Zipkin service
                'endpoint_url' => env('ZIPKIN_ENDPOINT_URL', 'http://localhost:9411/api/v2/spans'),
                // Request timeout (in seconds)
                'timeout' => env('ZIPKIN_TIMEOUT', 1),
            ],
            // Sampler, track all requests by default
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
    // default Tracer
    'default' => env('TRACER_DRIVER', 'jaeger'),

    // Other configurations are omitted here in this example
    'enable' => [],

    'tracer' => [
        // Jaeger drive config
        'jaeger' => [
            'driver' => JaegerTracerFactory::class,
            // project name
            'name' => env('APP_NAME', 'skeleton'),
            'options' => [
                // Sampler, track all requests by default
                'sampler' => [
                    'type' => SAMPLER_TYPE_CONST,
                    'param' => true,
                ],
                // the address which should report to
                'local_agent' => [
                    'reporting_host' => env('JAEGER_REPORTING_HOST', 'localhost'),
                    'reporting_port' => env('JAEGER_REPORTING_PORT', 5775),
                ],
            ],
        ],
    ],
];
```

More configurations about Jaeger can be found in [here](https://github.com/jonahgeorge/jaeger-client-php)].

#### Configure JsonRPC tracking enabling

JsonRPC's link tracking is not in the unified configuration, and temporarily belongs to the `Beta` version.

We only need to configure `aspects.php`, and add the following `Aspect` to enable it.

> Tip: Don’t forget to add the corresponding TraceMiddleware on the opposite site.

```php
<?php

return [
    Hyperf\Tracer\Aspect\JsonRpcAspect::class,
];
```

#### Configure coroutine tracking enabling

Coroutine link tracking is not included in the unified configuration, it is an optional version of the function.

We only need to configure `aspects.php` and add the following `Aspect` to enable it.

```php
<?php

return [
    Hyperf\Tracer\Aspect\CoroutineAspect::class,
];
```

### Configure middleware or listener

After configuring the driver, you need to configure the middleware or request cycle event listener to collect information to enable the collection function.

- Add middleware

Open the `config/autoload/middlewares.php` file and enable the middleware on the `http` node.

```php
<?php

declare(strict_types=1);

return [
     'http' => [
         \Hyperf\Tracer\Middleware\TraceMiddleware::class,
     ],
];
```

- or add a listener

Open the `config/autoload/listeners.php` file and add the listener.

```php
<?php

declare(strict_types=1);

return [
     \Hyperf\Tracer\Listener\RequestTraceListener::class,
];
```

### Configure Span tag

For some Span Tag names that Hyperf automatically collects tracking information for, you can change the corresponding name by changing the Span Tag configuration. Just add the `tags` configuration in the configuration file `config/autolaod/opentracing.php`. The reference configuration is as follows. If the configuration item exists, the value of the configuration item shall prevail. If the configuration item does not exist, the default value of the component shall prevail.

```php
return [
    'tags' => [
        // HTTP client (Guzzle)
        'http_client' => [
            'http.url' => 'http.url',
            'http.method' => 'http.method',
            'http.status_code' => 'http.status_code',
        ],
        // Redis client
        'redis' => [
            'arguments' => 'arguments',
            'result' => 'result',
        ],
        // database client (hyperf/database)
        'db' => [
            'db.query' => 'db.query',
            'db.statement' => 'db.statement',
            'db.query_time' => 'db.query_time',
        ],
    ]
];
```

### Replace the sampler

The default sampler records the call link for all requests, which will have a certain impact on performance, especially memory usage. So we only need to track the call link when we want, then we need to replace the sampler. It's easy to replace the sampler, take the Zipkin as an example, just change the corresponding value of the configuration item `opentracing.zipkin.sampler` to your sampler object instance, as long as your sampler object implements the `Zipkin\Sampler` interface class.

### Access to Alibaba Cloud link tracking service

When we are using Alibaba Cloud’s link tracking service, since the opposite end also supports the `Zipkin` protocol, you can directly modify the value of `endpoint_url` in the `config/autoload/opentracing.php` configuration file to your corresponding address of Aliyun `region`. The specific address can be obtained in Alibaba Cloud's link tracking service. For more details, please refer to [Alibaba Cloud Link Tracking Service Help Document](https://help.aliyun.com/document_detail/100031.html?spm=a2c4g.11186623.6.547.68f974dcZlg4Mv)

### Use other Tracer drivers

You can also use any other Tracer drivers which follows the OpenTracing protocol.  In the Driver field, fill any class that implements `Hyperf\Tracer\Contract\NamedFactoryInterface`. This interface has only one `make()` function, the parameter is the driver name, and it needs to return an instance that implements OpenTracing\Tracer.

## Reference
- [Opentracing](https://opentracing.io)
- [Zipkin](https://zipkin.io/)
- [Jaeger](https://www.jaegertracing.io/)
- [Dapper, tracking system for large-scale distributed systems](https://bigbully.github.io/Dapper-translation/)
