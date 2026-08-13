# Redis

## Installation

```shell
composer require hyperf/redis
```

## Configuration

| Configuration | Type | Default | Remark |
|:--------------:|:-------:|:-----------:|:------------------------------:|
|      host      | string  | 'localhost' |           Redis host            |
|      auth      | string  |     None    |              Password              |
|      port      | integer |    6379     |              Port              |
|       db       | integer |      0      |               DB               |
| cluster.enable | boolean |    false    |          Whether cluster mode enabled         |
|  cluster.name  | string  |    null     |             Cluster name             |
| cluster.seeds  |  array  |     []      | Cluster connection addresses array ['host:port'] |
|      pool      | object  |     {}      |           Connection pool configuration           |
|    options     | object  |     {}      |         Redis configuration options         |

```php
<?php
return [
    'default' => [
        'host' => env('REDIS_HOST', 'localhost'),
        'auth' => env('REDIS_AUTH', ''),
        'port' => (int) env('REDIS_PORT', 6379),
        'db' => (int) env('REDIS_DB', 0),
        'cluster' => [
            'enable' => (bool) env('REDIS_CLUSTER_ENABLE', false),
            'name' => null,
            'seeds' => [],
        ],
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float) env('REDIS_MAX_IDLE_TIME', 60),
        ],
        'options' => [ // Redis client Options, refer to https://github.com/phpredis/phpredis#setoption
            \Redis::OPT_PREFIX => env('REDIS_PREFIX', ''),
            // or 'prefix' => env('REDIS_PREFIX', ''), for v3.0.38 or higher
        ],
    ],
];
```

To publish the complete configuration file, use the command:

```shell
php bin/hyperf.php vendor:publish hyperf/redis
```

## Usage

`hyperf/redis` implements the `ext-redis` proxy and connection pool. Users can directly inject `\Hyperf\Redis\Redis` through the dependency injection container to use the Redis client. What you actually get is a proxy object of `\Redis`.

```php
<?php
use Hyperf\Context\ApplicationContext;

$container = ApplicationContext::getContainer();

$redis = $container->get(\Hyperf\Redis\Redis::class);
$result = $redis->keys('*');
```

## Multi-Database Configuration

Sometimes in actual use, a single `Redis` database is not enough, and a project often needs to configure multiple databases. At this time, we need to modify the configuration file `redis.php` as follows:

```php
<?php

return [
    'default' => [
        'host' => env('REDIS_HOST', 'localhost'),
        'auth' => env('REDIS_AUTH', ''),
        'port' => (int) env('REDIS_PORT', 6379),
        'db' => (int) env('REDIS_DB', 0),
        'cluster' => [
            'enable' => (bool) env('REDIS_CLUSTER_ENABLE', false),
            'name' => null,
            'seeds' => [],
        ],
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float) env('REDIS_MAX_IDLE_TIME', 60),
        ],
    ],
    // Add a Redis connection pool named 'foo'
    'foo' => [
        'host' => env('REDIS_HOST', 'localhost'),
        'auth' => env('REDIS_AUTH', ''),
        'port' => (int) env('REDIS_PORT', 6379),
        'db' => 1,
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float) env('REDIS_MAX_IDLE_TIME', 60),
        ],
    ],
];
```

### Using via Proxy Class

We can rewrite a `FooRedis` class and inherit the `Hyperf\Redis\Redis` class, and change the `poolName` to the above `foo`, which completes the switch of the connection pool. Example:

```php
<?php
use Hyperf\Redis\Redis;

class FooRedis extends Redis
{
    // The key value of the corresponding Pool
    protected $poolName = 'foo';
}

// Get the current class through the DI container or inject it directly
$redis = $this->container->get(FooRedis::class);

$result = $redis->keys('*');
```

### Using Factory Class

When each database corresponds to a fixed usage scenario, using a proxy class is a good way to distinguish them. But sometimes needs may be more dynamic. In this case, we can use the `Hyperf\Redis\RedisFactory` factory class to dynamically pass the `poolName` to get the client of the corresponding connection pool, without creating a proxy class for each database. Example:

```php
<?php
use Hyperf\Redis\RedisFactory;
use Hyperf\Context\ApplicationContext;

$container = ApplicationContext::getContainer();

// Get the RedisFactory class through the DI container or inject it directly
$redis = $container->get(RedisFactory::class)->get('foo');
$result = $redis->keys('*');
```

## Sentinel Mode

To enable Sentinel mode, you can modify it in the `.env` or `redis.php` configuration file as follows:

Separate multiple sentinel nodes with `;`

```env
REDIS_HOST=
REDIS_AUTH=Redis instance password
REDIS_PORT=
REDIS_DB=
REDIS_SENTINEL_ENABLE=true
REDIS_SENTINEL_PASSWORD=Redis sentinel password
REDIS_SENTINEL_NODE=192.168.89.129:26381;192.168.89.129:26380;
```

```php
<?php

return [
    'default' => [
        'host' => env('REDIS_HOST', 'localhost'),
        'auth' => env('REDIS_AUTH', null),
        'port' => (int) env('REDIS_PORT', 6379),
        'db' => (int) env('REDIS_DB', 0),
        'timeout' => 30.0,
        'reserved' => null,
        'retry_interval' => 0,
        'sentinel' => [
            'enable' => (bool) env('REDIS_SENTINEL_ENABLE', false),
            'master_name' => env('REDIS_MASTER_NAME', 'mymaster'),
            'nodes' => explode(';', env('REDIS_SENTINEL_NODE', '')),
            'persistent' => false,
            'read_timeout' => 30.0,
            'auth' =>  env('REDIS_SENTINEL_PASSWORD', ''),
        ],
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float) env('REDIS_MAX_IDLE_TIME', 60),
        ],
    ],
];
```

## Cluster Mode

### Using `name`

Configure `cluster`, modify `redis.ini`, or you can also modify `Dockerfile` as follows:

```shell
    # - config PHP
    && { \
        echo "upload_max_filesize=100M"; \
        echo "post_max_size=108M"; \
        echo "memory_limit=1024M"; \
        echo "date.timezone=${TIMEZONE}"; \
        echo "redis.clusters.seeds = \"mycluster[]=localhost:7000&mycluster[]=localhost:7001\""; \
        echo "redis.clusters.timeout = \"mycluster=5\""; \
        echo "redis.clusters.read_timeout = \"mycluster=10\""; \
        echo "redis.clusters.auth = \"mycluster=password\"";
    } | tee conf.d/99-overrides.ini \
```

The corresponding PHP configuration is as follows:

```php
<?php
// Omitted other configurations
return [
    'default' => [
        'cluster' => [
            'enable' => true,
            'name' => 'mycluster',
            'seeds' => [],
        ],
    ],
];
```

### Using `seeds`

Of course, you can also use `seeds` directly without configuring `name`. As follows:

```php
<?php
// Omitted other configurations
return [
    'default' => [
        'cluster' => [
            'enable' => true,
            'name' => null,
            'seeds' => [
                '192.168.1.110:6379',
                '192.168.1.111:6379',
            ],
        ],
    ],
];
```

## Options

Users can modify `options` to set `Redis` configuration options.

For example, modify `Redis` serialization to `PHP` serialization.

```php
<?php

declare(strict_types=1);

return [
    'default' => [
        'host' => env('REDIS_HOST', 'localhost'),
        'auth' => env('REDIS_AUTH', null),
        'port' => (int) env('REDIS_PORT', 6379),
        'db' => (int) env('REDIS_DB', 0),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float) env('REDIS_MAX_IDLE_TIME', 60),
        ],
        'options' => [
            \Redis::OPT_SERIALIZER => \Redis::SERIALIZER_PHP,
            // or 'serializer' => \Redis::SERIALIZER_PHP, for v3.0.38 or higher
        ],
    ],
];
```

For example, set `Redis` to never timeout:

```php
<?php

declare(strict_types=1);

return [
    'default' => [
        'host' => env('REDIS_HOST', 'localhost'),
        'auth' => env('REDIS_AUTH', null),
        'port' => (int) env('REDIS_PORT', 6379),
        'db' => (int) env('REDIS_DB', 0),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float) env('REDIS_MAX_IDLE_TIME', 60),
        ],
        'options' => [
            \Redis::OPT_READ_TIMEOUT => -1,
            // or 'read_timeout' => -1, for v3.1.3 or higher
        ],
    ],
];
```

> For some `phpredis` extension versions, the `value` of `option` must be of `string` type.
