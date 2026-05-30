# Guzzle HTTP Client

The [hyperf/guzzle](https://github.com/hyperf/guzzle) component performs coroutine processing based on Guzzle. It replaces the Guzzle handler with a Swoole HTTP client as the coroutine driver to achieve coroutine-friendly HTTP client operation.

## Installation

```bash
composer require hyperf/guzzle
```

## Usage

Simply set `Hyperf\Guzzle\CoroutineHandler` as the handler in the Guzzle client to enable coroutine-friendly operation. To facilitate the creation of coroutine-friendly Guzzle objects, we provide a factory class `Hyperf\Guzzle\ClientFactory` for convenient client creation. Example code:

```php
<?php 
use Hyperf\Guzzle\ClientFactory;

class Foo
{
    private ClientFactory $clientFactory;

    public function __construct(ClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
    }
    
    public function bar()
    {
        // $options is the same as the $config parameter of the GuzzleHttp\Client constructor
        $options = [];
        // $client is the coroutine-friendly GuzzleHttp\Client object
        $client = $this->clientFactory->create($options);
    }
}
```

### Using ^7.0 version

The component's dependency on `Guzzle` has been changed from `^6.3` to `^6.3 \| ^7.0`. By default, it is possible to install the `^7.0` version, but the following components conflict with `^7.0`.

- hyperf/metric

You can actively execute the following operations to resolve the conflict:

```
composer require "promphp/prometheus_client_php:2.2.1"
```

- overtrue/flysystem-cos

Because the dependency library depends on `guzzlehttp/guzzle-services`, which does not support `^7.0`, it cannot be solved for now.

## Using Swoole Configuration

Sometimes we want to directly modify `Swoole` configuration, so we also provide relevant configuration items. However, this configuration will not take effect in the `Curl Guzzle client`, so use it with caution.

> This configuration will replace the original configuration. For example, the following timeout will be replaced by 10.

```php
<?php
use GuzzleHttp\Client;
use Hyperf\Guzzle\CoroutineHandler;
use GuzzleHttp\HandlerStack;

$client = new Client([
    'base_uri' => 'http://127.0.0.1:8080',
    'handler' => HandlerStack::create(new CoroutineHandler()),
    'timeout' => 5,
    'swoole' => [
        'timeout' => 10,
        'socket_buffer_size' => 1024 * 1024 * 2,
    ],
]);

$response = $client->get('/');

```

## Connection Pool

In addition to implementing `Hyperf\Guzzle\CoroutineHandler`, Hyperf also implements `Hyperf\Guzzle\PoolHandler` based on `Hyperf\Pool\SimplePool`.

### Reason

Simply put, the number of TCP connections on a host is limited. When concurrency is high enough to exceed this limit, requests cannot be established normally. In addition, there is a TIME-WAIT phase after a TCP connection ends, so the connection cannot be released in real-time. This leads to the actual concurrency being far lower than the TCP limit. Therefore, we need a connection pool to maintain this stage, minimize the impact caused by TIME-WAIT, and allow TCP connections to be reused.

### Usage

```php
<?php
use GuzzleHttp\Client;
use Hyperf\Coroutine\Coroutine;
use GuzzleHttp\HandlerStack;
use Hyperf\Guzzle\PoolHandler;
use Hyperf\Guzzle\RetryMiddleware;

$handler = null;
if (Coroutine::inCoroutine()) {
    $handler = make(PoolHandler::class, [
        'option' => [
            'max_connections' => 50,
        ],
    ]);
}

// Default retry Middleware
$retry = make(RetryMiddleware::class, [
    'retries' => 1,
    'delay' => 10,
]);

$stack = HandlerStack::create($handler);
$stack->push($retry->getMiddleware(), 'retry');

$client = make(Client::class, [
    'config' => [
        'handler' => $stack,
    ],
]);
```

In addition, the framework also provides `HandlerStackFactory` to facilitate the creation of the above `$stack`.

```php
<?php
use Hyperf\Guzzle\HandlerStackFactory;
use GuzzleHttp\Client;

$factory = new HandlerStackFactory();
$stack = $factory->create();

$client = make(Client::class, [
    'config' => [
        'handler' => $stack,
    ],
]);
```

## Using `ClassMap` to replace `GuzzleHttp\Client`

If third-party components do not provide an interface to replace the `Handler`, we can also use the `ClassMap` function to directly replace the `Client` to achieve the purpose of making the client coroutine-friendly.

> Of course, you can also use `SWOOLE_HOOK` to achieve the same purpose.

Code example:

class_map/GuzzleHttp/Client.php

```php
<?php
namespace GuzzleHttp;

use GuzzleHttp\Psr7;
use Hyperf\Guzzle\CoroutineHandler;
use Hyperf\Coroutine\Coroutine;

class Client implements ClientInterface
{
    // Code omitted for other unchanged parts

    public function __construct(array $config = [])
    {
        $inCoroutine = Coroutine::inCoroutine();
        if (!isset($config['handler'])) {
            // The corresponding Handler can be selected as CoroutineHandler or PoolHandler as needed
            $config['handler'] = HandlerStack::create($inCoroutine ? new CoroutineHandler() : null);
        } elseif ($inCoroutine && $config['handler'] instanceof HandlerStack) {
            $config['handler']->setHandler(new CoroutineHandler());
        } elseif (!is_callable($config['handler'])) {
            throw new \InvalidArgumentException('handler must be a callable');
        }

        // Convert the base_uri to a UriInterface
        if (isset($config['base_uri'])) {
            $config['base_uri'] = Psr7\uri_for($config['base_uri']);
        }

        $this->configureDefaults($config);
    }
}
```

config/autoload/annotations.php

```php
<?php

declare(strict_types=1);

use GuzzleHttp\Client;

return [
    'scan' => [
        // ...
        'class_map' => [
            Client::class => BASE_PATH . '/class_map/GuzzleHttp/Client.php',
        ],
    ],
];
```
