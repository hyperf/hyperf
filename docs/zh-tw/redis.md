# Redis

## 安裝

```shell
composer require hyperf/redis
```

## 配置

|     配置項     |  型別   |   預設值    |              備註              |
|:--------------:|:-------:|:-----------:|:------------------------------:|
|      host      | string  | 'localhost' |           Redis 地址            |
|      auth      | string  |     無      |              密碼              |
|      port      | integer |    6379     |              埠              |
|       db       | integer |      0      |               DB               |
| cluster.enable | boolean |    false    |          是否叢集模式          |
|  cluster.name  | string  |    null     |             叢集名             |
| cluster.seeds  |  array  |     []      | 叢集連線地址陣列 ['host:port'] |
|      pool      | object  |     {}      |           連線池配置           |
|    options     | object  |     {}      |         Redis 配置選項         |

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
        'options' => [ // Redis 客戶端 Options, 參照 https://github.com/phpredis/phpredis#setoption
            \Redis::OPT_PREFIX => env('REDIS_PREFIX', ''),
            // or 'prefix' => env('REDIS_PREFIX', ''), v3.0.38 或更高版本
        ],
    ],
];

```

`publish`完整配置檔案使用命令

```shell
php bin/hyperf.php vendor:publish hyperf/redis
```

## 使用

`hyperf/redis` 實現了 `ext-redis` 代理和連線池，使用者可以直接透過依賴注入容器注入 `\Hyperf\Redis\Redis` 來使用 Redis 客戶端，實際獲得的是 `\Redis` 的一個代理物件。

```php
<?php
use Hyperf\Context\ApplicationContext;

$container = ApplicationContext::getContainer();

$redis = $container->get(\Hyperf\Redis\Redis::class);
$result = $redis->keys('*');
```

## 多庫配置

有時候在實際使用中，一個 `Redis` 庫並不滿足需求，一個專案往往需要配置多個庫，這個時候，我們就需要修改一下配置檔案 `redis.php`，如下：

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
    // 增加一個名為 foo 的 Redis 連線池
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

### 透過代理類使用

我們可以重寫一個 `FooRedis` 類並繼承 `Hyperf\Redis\Redis` 類，修改 `poolName` 為上述的 `foo`，即可完成對連線池的切換，示例：

```php
<?php
use Hyperf\Redis\Redis;

class FooRedis extends Redis
{
    // 對應的 Pool 的 key 值
    protected $poolName = 'foo';
}

// 透過 DI 容器獲取或直接注入當前類
$redis = $this->container->get(FooRedis::class);

$result = $redis->keys('*');

```

### 使用工廠類

在每個庫對應一個固定的使用場景時，透過代理類是一種很好的區分的方法，但有時候需求可能會更加的動態，這時候我們可以透過 `Hyperf\Redis\RedisFactory` 工廠類來動態的傳遞 `poolName` 來獲得對應的連線池的客戶端，而無需為每個庫建立代理類，示例如下：

```php
<?php
use Hyperf\Redis\RedisFactory;
use Hyperf\Context\ApplicationContext;

$container = ApplicationContext::getContainer();

// 透過 DI 容器獲取或直接注入 RedisFactory 類
$redis = $container->get(RedisFactory::class)->get('foo');
$result = $redis->keys('*');
```

## 哨兵模式

開啟哨兵模式可以在`.env`或 `redis.php` 配置檔案中修改如下

多個哨兵節點使用`;`分割

```env
REDIS_HOST=
REDIS_AUTH=Redis例項密碼
REDIS_PORT=
REDIS_DB=
REDIS_SENTINEL_ENABLE=true
REDIS_SENTINEL_PASSWORD=Redis哨兵密碼
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

## 叢集模式

### 使用 `name`

配置 `cluster`，修改修改 `redis.ini`，也可以修改 `Dockerfile` 如下

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

對應 PHP 配置如下

```php
<?php
// 省略其他配置
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

### 使用 seeds

當然不配置 name 直接使用 seeds 也是可以的。如下

```php
<?php
// 省略其他配置
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

使用者可以修改 `options`，來設定 `Redis` 配置選項。

例如修改 `Redis` 序列化為 `PHP` 序列化。

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
            // 或者 'serializer' => \Redis::SERIALIZER_PHP, v3.0.38 或更高版本
        ],
    ],
];
```

比如設定 `Redis` 永不超時

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
            // 或者 'read_timeout' => -1, v3.1.3 或更高版本
        ],
    ],
];
```

> 有的 `phpredis` 擴充套件版本，`option` 的 `value` 必須是 `string` 型別。
