# Pool

## Installation

```bash
composer require hyperf/pool
```

## Why the pool needed?

When the amount of concurrency is very low, the connection can be temporarily established. However, when the service throughput reaches hundreds or thousands of magnitude, frequent `Connect` and `Close` may become a bottleneck of the service. Practically, when the service is started, several connections can be established and stored in a queue. When needed, one is taken from the queue and used, and then returned to the queue after use. The data structure of this queue is maintained by the connection pool.

## Usage

For the components provided by Hyperf, the connection pool has been adapted. No perception in use. Hyperf automatically completes the acquisition and return of the connection.

## Custom connection pool

To define a connection pool, you first need to implement a subclass that inherits `Hyperf\Pool\Pool` and implements the abstract method `createConnection`, and an object that implements the `Hyperf\Contract\ConnectionInterface` interface should be returned. A demo shown as follow:
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
In this way, the connection can be taken and returned by calling the methods of `get(): ConnectionInterface` and `release(ConnectionInterface $connection): void` on the instantiated `MyConnectionPool` object.

## SimplePool

A simple pool implementation is provided by hyperf.

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

$client = $connection->getConnection(); // The Client which mentioned above.

// Do something.

$connection->release();

```

## Low-frequency Interface

The pool has a built-in `LowFrequencyInterface` interface. The low-frequency component used by default, and determine whether to release excess connections in the pool based on the frequency of acquiring connections from the pool.

If we need to replace the corresponding low-frequency component, you can directly replace it in the `dependencies` configuration. Take the database component as an example.

```php
<?php

declare(strict_types=1);

namespace App\Pool;

class Frequency extends \Hyperf\Pool\Frequency
{
    /**
     * The time interval of the calculated frequency
     * @var int
     */
    protected $time = 10;

    /**
     * Threshold
     * @var int
     */
    protected $lowFrequency = 5;

    /**
     * Minimum time interval for continuous low frequency triggering
     * @var int
     */
    protected $lowFrequencyInterval = 60;
}

```

Modify the mapping as follows

```php
<?php
return [
    Hyperf\DbConnection\Frequency::class => App\Pool\Frequency::class,
];
```

### Constant frequency

Hyperf also provides another low-frequency component `ConstantFrequency`.

When this component is instantiated, a timer will be started and the method `Pool::flushOne(false)` will be called at a regular interval. This method will take a connection from the pool and a connection will be destroyed when the method judged it has been idle for more than a period of time.

```php
<?php
return [
    Hyperf\DbConnection\Frequency::class => Hyperf\Pool\ConstantFrequency::class,
];
```
