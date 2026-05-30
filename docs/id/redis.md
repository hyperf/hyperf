# Redis

## Instalasi

```shell
composer require hyperf/redis
```

## Konfigurasi

| Konfigurasi |  Tipe   |   Nilai Default    |   Keterangan    |
|:------:|:-------:|:-----------:|:---------:|
|  host  | string  | 'localhost' | Host dari Redis Server |
|  auth  | string  |     null      |   Password dari Redis Server    |
|  port  | integer |    6379     |   Port dari Redis Server    |
|   db   | integer |      0      |    DB dari Redis Server     |
| cluster.enable | boolean |    false    |          Apakah mode cluster?          |
|  cluster.name  | string  |    null     |             Nama cluster             |
| cluster.seeds  |  array  |     []      | Seed dari cluster, format: ['host:port'] |
|      pool      | object  |     {}      |           Connection pool           |
|    options     | object  |     {}      |         Opsi dari Redis Client         |

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
        'options' => [ // Options of Redis Client, see https://github.com/phpredis/phpredis#setoption
            \Redis::OPT_PREFIX => env('REDIS_PREFIX', ''),
            // or 'prefix' => env('REDIS_PREFIX', ''), v3.0.38 or later
        ],
    ],
];

```

Gunakan perintah berikut untuk mempublikasikan (`publish`) file konfigurasi
lengkap:

```shell
php bin/hyperf.php vendor:publish hyperf/redis
```

## Penggunaan

`hyperf/redis` mengimplementasikan proxy dari `ext-redis` dan connection pool.
Pengguna dapat langsung menginjeksikan `\Hyperf\Redis\Redis` melalui container
dependency injection untuk menggunakan Redis client. Objek yang didapatkan
sebenarnya adalah proxy dari objek `\Redis`.

```php
<?php

use Hyperf\Context\ApplicationContext;

$container = ApplicationContext::getContainer();

$redis = $container->get(\Hyperf\Redis\Redis::class);
$result = $redis->keys('*');

```

## Konfigurasi multi-resource

Terkadang, satu resource `Redis` tidak dapat memenuhi kebutuhan, dan sebuah
proyek sering kali perlu mengonfigurasi beberapa resource sekaligus. Pada saat
ini, kita dapat mengubah file konfigurasi `redis.php` sebagai berikut:

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
    // Tambahkan connection pool Redis baru bernama foo
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

### Menggunakan melalui class proxy

Kita dapat menulis ulang sebuah class `FooRedis` dan mewarisi (inherit) class
`Hyperf\Redis\Redis`, lalu mengubah properti `poolName` ke nama pool di atas,
yaitu `foo`, untuk menyelesaikan peralihan connection pool, contohnya:

```php
<?php

use Hyperf\Redis\Redis;

class FooRedis extends Redis
{
    // Nilai key dari Pool yang sesuai
    protected $poolName = 'foo';
}

// Dapatkan atau langsung injeksikan class saat ini melalui DI container
$redis = $this->container->get(FooRedis::class);

$result = $redis->keys('*');

```

### Menggunakan melalui factory

Ketika setiap resource sesuai dengan skenario statis, class proxy adalah cara
yang baik untuk membedakan resource, tetapi terkadang kebutuhan bisa lebih
dinamis. Pada saat ini, kita dapat menggunakan factory class
`Hyperf\Redis\RedisFactory` untuk meneruskan argumen `poolName` secara dinamis
guna mengambil client dari connection pool yang sesuai tanpa membuat class
proxy untuk setiap resource, contohnya:

```php
<?php
use Hyperf\Redis\RedisFactory;
use Hyperf\Context\ApplicationContext;

$container = ApplicationContext::getContainer();

// Dapatkan atau langsung injeksikan class RedisFactory melalui DI container
$redis = $container->get(RedisFactory::class)->get('foo');
$result = $redis->keys('*');
```

## Mode Sentinel

Untuk mengaktifkan mode sentinel, Anda dapat mengubah file konfigurasi `.env`
atau `redis.php` sebagai berikut:

Gunakan `;` untuk memisahkan beberapa node sentinel

```env
REDIS_HOST=
REDIS_AUTH="Redis instance password"
REDIS_PORT=
REDIS_DB=
REDIS_SENTINEL_ENABLE=true
REDIS_SENTINEL_PASSWORD="Redis sentinel password"
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

## Mode Cluster

### Menggunakan `name`

Konfigurasikan `cluster`, ubah `redis.ini`, atau ubah `Dockerfile`, sebagai
berikut:

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

Konfigurasi PHP yang sesuai adalah sebagai berikut:

```php
<?php
// Abaikan konfigurasi lain yang tidak relevan
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

### Menggunakan seeds

Tentu saja, Anda juga dapat menggunakan `seeds` secara langsung tanpa
mengonfigurasi `name`, sebagai berikut:

```php
<?php
// Abaikan konfigurasi lain yang tidak relevan
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

## Opsi (Options)

Pengguna dapat mengubah `options` untuk mengatur opsi konfigurasi `Redis`.

Sebagai contoh, mengubah serialisasi `Redis` menjadi serialisasi `PHP`.

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
            // or 'serializer' => \Redis::SERIALIZER_PHP, v3.0.38 or later
        ],
    ],
];
```

Sebagai contoh, mengatur agar `Redis` tidak pernah timeout:

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
            // or 'read_timeout' => -1, v3.0.38 or later
        ],
    ],
];
```

> Perhatikan bahwa pada beberapa versi ekstensi `phpredis`, tipe nilai dari
> `options` harus berupa `string`.
