# Guzzle HTTP Client

The [hyperf/guzzle](https://github.com/hyperf/guzzle) component is based on Guzzle for coroutine processing, and is replaced into Guzzle through the Swoole HTTP client as a coroutine driver to achieve the coroutineization of the HTTP client.

## Installation

```bash
composer require hyperf/guzzle
```

## Application

Just set the `Hyperf\Guzzle\CoroutineHandler` in this component into the Guzzle client as a handler to convert into a coroutine operation. In order to facilitate the creation of the Guzzle object of the coroutine, we provide a factory class `Hyperf\Guzzle\ClientFactory` to conveniently create the client. Example is as follow:

```php
<?php 
use Hyperf\Guzzle\ClientFactory;

class Foo {
    /**
     * @var \Hyperf\Guzzle\ClientFactory
     */
    private $clientFactory;
    
    public function __construct(ClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
    }
    
    public function bar()
    {
        // $options is equivalent to the $config parameter of the GuzzleHttp\Client constructor
        $options = [];
        // $client is a coroutineized GuzzleHttp\Client object
        $client = $this->clientFactory->create($options);
    }
}
```

### Use ^7.0 version

The component's dependency on `Guzzle` has been changed from `^6.3` to `^6.3 | ^7.0`. The `^7.0` version can be installed by default, but the following components will conflict with `^7.0`:

- hyperf/metric

You can actively perform the following actions to resolve conflicts

```
composer require "promphp/prometheus_client_php:2.2.1"
```

- overtrue/flysystem-cos

Due to the dependent library depends on `guzzlehttp/guzzle-services`, and it does not support `^7.0`, it cannot be resolved temporarily.

## Use Swoole configuration

Sometimes we want to modify the `Swoole` configuration directly, so we also provide related configuration items. But this configuration cannot take effect in the `Curl Guzzle client`, so use it carefully.

> This configuration will replace the original configuration. For example, the timeout below will be replaced by 10.

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

Hyperf not only implements `Hyperf\Guzzle\CoroutineHandler`, but also implements `Hyperf\Guzzle\PoolHandler` based on `Hyperf\Pool\SimplePool`.

### Why

There is an upper limit on the number of host TCP connections. When our concurrency exceeds this upper limit, the request cannot be established normally. In addition, there will be a TIME-WAIT after the TCP connection ends, so the connection cannot be released in time. Therefore, we need a connection pool to maintain this stage, minimize the impact of TIME-WAIT, and allow TCP connections to be reused.

### Application

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

// Default retry middleware
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

In addition, the framework also provides `HandlerStackFactory` to conveniently create the above `$stack`.

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

## Use `ClassMap` to replace `GuzzleHttp\Client`

If the third-party component does not provide an interface that can replace the `Handler`, we can also use the `ClassMap` to directly replace the `Client` to achieve the purpose of coroutineization of client.

> Of course, you can also use SWOOLE_HOOK to achieve the same purpose.

Example is as follow:

class_map/GuzzleHttp/Client.php

```php
<?php
namespace GuzzleHttp;

use GuzzleHttp\Psr7;
use Hyperf\Guzzle\CoroutineHandler;
use Hyperf\Coroutine\Coroutine;

class Client implements ClientInterface
{
    // Omitted other unchanged codes

    public function __construct(array $config = [])
    {
        $inCoroutine = Coroutine::inCoroutine();
        if (!isset($config['handler'])) {
            // The corresponding Handler can choose CoroutineHandler or PoolHandler as needed
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
