# Connection Pool

## Installation

```bash
composer require hyperf/pool
```

## Why do we need a connection pool?

When concurrency is low, connections can be established on demand. However, when service throughput reaches hundreds or thousands of requests, frequent `Connect` and `Close` operations can become a bottleneck. By establishing a set of connections when the service starts and keeping them in a queue, you can retrieve a connection when needed, use it, and return it to the queue afterward. Maintaining this queue is the responsibility of the connection pool.

## Using a connection pool

For official Hyperf components, connection pools are already integrated. You do not need to manage them explicitly; the underlying framework automatically handles the acquisition and release of connections.

## Custom connection pool

To define a connection pool, you first need to implement a subclass that extends `Hyperf\Pool\Pool` and implement the abstract method `createConnection`. This method must return an object that implements the `Hyperf\Contract\ConnectionInterface` interface. Your custom connection pool is then ready to use. See the example below:

```php
<?php
namespace App\Pool;

use Hyperf\Contract\ConnectionInterface;
use Hyperf\Pool\Pool;

class MyConnectionPool extends Pool
{
    public function createConnection(): ConnectionInterface
    {
        return new MyConnection();
    }
}
```

After instantiation, you can use the `get(): ConnectionInterface` and `release(ConnectionInterface $connection): void` methods of the `MyConnectionPool` object to acquire and release connections.

## SimplePool

The framework provides a very simple connection pool implementation.

```php
<?php

use Hyperf\Pool\SimplePool\PoolFactory;
use Swoole\Coroutine\Http\Client;

$factory = $container->get(PoolFactory::class);

$pool = $factory->get('your pool name', function () use ($host, $port, $ssl) {
    return new Client($host, $port, $ssl);
}, [
    'max_connections' => 50
]);

$connection = $pool->get();

$client = $connection->getConnection(); // The Client instance mentioned above.

// Do something.

$connection->release();

```

## Low-frequency component

The connection pool comes with a built-in `LowFrequencyInterface` for handling low-frequency connections. By default, it uses this component to determine whether to release excess connections from the pool based on the frequency of connection acquisition.

If you need to replace the low-frequency component, you can directly replace it in the `dependencies` configuration. Below is an example using the database component:

```php
<?php

declare(strict_types=1);

namespace App\Pool;

class Frequency extends \Hyperf\Pool\Frequency
{
    /**
     * Time interval for frequency calculation
     */
    protected int $time = 10;

    /**
     * Frequency to trigger the low-frequency handler
     */
    protected int $lowFrequency = 5;

    /**
     * Minimum time interval between consecutive low-frequency triggers
     */
    protected int $lowFrequencyInterval = 60;
}

```

Modify the mapping relationship as follows:

```php
<?php
return [
    Hyperf\DbConnection\Frequency::class => App\Pool\Frequency::class,
];
```

### Constant Frequency

The framework also provides another low-frequency component called `ConstantFrequency`.

Once this component is instantiated, it starts a timer that calls the `Pool::flushOne(false)` method at fixed intervals. This method retrieves one connection from the pool and destroys it if it has exceeded the idle time limit.

```php
<?php
return [
    Hyperf\DbConnection\Frequency::class => Hyperf\Pool\ConstantFrequency::class,
];
```
