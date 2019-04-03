# Redis

## 安装

```
composer require hyperf/redis
```

## 配置

| 配置项 |  类型   |   默认值    |   备注    |
|:------:|:-------:|:-----------:|:---------:|
|  host  | string  | 'localhost' | Redis地址 |
|  auth  | string  |     无      |   密码    |
|  port  | integer |    6379     |   端口    |
|   db   | integer |      0      |    DB     |

```php
<?php

return [
    'default' => [
        'host' => env('REDIS_HOST', 'localhost'),
        'auth' => env('REDIS_AUTH', ''),
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
    ],
];

```

## 使用

`hyperf/redis` 实现了 `ext-redis` 代理和连接池，用户可以直接使用\Redis客户端。

```php
<?php

$redis = $this->container->get(\Redis::class);

$result = $redis->keys('*');

```

## 多库配置

有时候在实际使用中，一个Redis库并不满足需求，需要用户在项目中配置多个。这个时候，我们就需要修改一下配置文件 `redis.php`。

```php
<?php

return [
    'default' => [
        'host' => env('REDIS_HOST', 'localhost'),
        'auth' => env('REDIS_AUTH', ''),
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
    ],
    'redis2'=>[
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

然后我们重写一个 Redis类 继承 `Hyperf\Redis\Redis`，置顶 poolName 为上述 redis2，示例如下

```php
use Hyperf\Redis\Redis;

class UserRedis extends Redis
{
    protected $poolName = 'redis2';
}

$redis = $this->container->get(UserRedis::class);

$result = $redis->keys('*');

```