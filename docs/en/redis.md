# Redis

## Installation

```
composer require hyperf/redis
```

## Configuration

| Config |  Type   |   Default Value    |   Comment    |
|:------:|:-------:|:-----------:|:---------:|
|  host  | string  | 'localhost' | The host of Redis Server |
|  auth  | string  |     null      |   The password of Redis Server    |
|  port  | integer |    6379     |   The port of Redis Server    |
|   db   | integer |      0      |    The DB of Redis Server     |
| cluster.enable | boolean |    false    |          Is it cluster mode ?          |
|  cluster.name  | string  |    null     |             The cluster name             |
| cluster.seeds  |  array  |     []      | The seeds of cluster, format: ['host:port'] |
|      pool      | object  |     {}      |           The connection pool           |
|    options     | object  |     {}      |         The options of Redis Client         |
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
];

```

## Usage

`hyperf/redis` implemented the proxy of `ext-redis` and the connection pool, you could use `\Redis` class directly.

```php
<?php

$redis = $this->container->get(\Redis::class);

$result = $redis->keys('*');

```

## Multi-resource configuration

Sometimes, a single `Redis` resource can not meet the needs, and a project often needs to configure multiple resources. At this time, we could modify the configuration file `redis.php` as follows:

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
    // Added a named `foo` redis connection pool
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

### Use through proxy class

We could rewrite a `FooRedis` class and inherit the `Hyperf\Redis\Redis` class, and modify the `poolName` property to the above `foo`, to complete the switch of the connection pool, for example:

```php
<?php

use Hyperf\Redis\Redis;

class FooRedis extends Redis
{
    // The key value of the corresponding Pool
    protected $poolName = 'foo';
}

// Obtain or directly inject the current class through the DI container
$redis = $this->container->get(FooRedis::class);

$result = $redis->keys('*');

```

### Use through factory

When each resource corresponds to a static scene, the proxy class is a good way to distinguish the resources, but sometimes the demand may be more dynamic. At this time, we could use the `Hyperf\Redis\RedisFactory` factory class to dynamically pass `poolName` argument to retrieve the client of the corresponding connection pool without creating a proxy class for each resource, for example:

```php
<?php

use Hyperf\Redis\RedisFactory;

// Obtain or directly inject the RedisFactory class through the DI container
$redis = $this->container->get(RedisFactory::class)->get('foo');

$result = $redis->keys('*');
```

## Cluster mode

### Cluster name
 
Configure `cluster`, modify `redis.ini`, or modify `Dockerfile`, as follows:

```
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

The corresponding PHP configuration is as follows

```php
<?php
// ./config/autoload/redis.php
// Ignore the other irrelevant configurations
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

### Seeds

Of course, it is also available to use `seeds` directly without configuring the `name`, as follows:

```php
<?php
// Ignore the other irrelevant configurations
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

You could define `options` configuration to set the options of Redis Client.

For example, use PHP Serializer to serialize the result:

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
            Redis::OPT_SERIALIZER => Redis::SERIALIZER_PHP,
        ],
    ],
];
```

For example, set the redis client never timeout:

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
            Redis::OPT_READ_TIMEOUT => -1,
        ],
    ],
];
```

> Notice that, in some versions of `phpredis` extension, the value type of `options` has to `string`.

