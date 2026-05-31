# Redis

## Instalasi

```shell
composer require hyperf/redis
```

## Konfigurasi

| Konfigurasi | Tipe | Default | Keterangan |
|:--------------:|:-------:|:-----------:|:------------------------------:|
|      host      | string  | 'localhost' |           Host Redis           |
|      auth      | string  |     None    |           Password             |
|      port      | integer |    6379     |           Port                 |
|       db       | integer |      0      |           DB                   |
| cluster.enable | boolean |    false    |   Apakah mode cluster diaktifkan |
|  cluster.name  | string  |    null     |           Nama cluster         |
| cluster.seeds  |  array  |     []      | Array alamat koneksi cluster ['host:port'] |
|      pool      | object  |     {}      | Konfigurasi connection pool    |
|    options     | object  |     {}      | Opsi konfigurasi Redis         |

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
        'options' => [ // Opsi Redis client, lihat https://github.com/phpredis/phpredis#setoption
            \Redis::OPT_PREFIX => env('REDIS_PREFIX', ''),
            // atau 'prefix' => env('REDIS_PREFIX', ''), untuk v3.0.38 atau lebih tinggi
        ],
    ],
];
```

Untuk mempublikasikan file konfigurasi lengkap, gunakan perintah:

```shell
php bin/hyperf.php vendor:publish hyperf/redis
```

## Penggunaan

`hyperf/redis` mengimplementasikan proxy `ext-redis` dan connection pool. Pengguna dapat langsung menginjeksi `\Hyperf\Redis\Redis` melalui container dependency injection untuk menggunakan Redis client. Yang Anda dapatkan sebenarnya adalah objek proxy dari `\Redis`.

```php
<?php
use Hyperf\Context\ApplicationContext;

$container = ApplicationContext::getContainer();

$redis = $container->get(\Hyperf\Redis\Redis::class);
$result = $redis->keys('*');
```

## Konfigurasi Multi-Database

Terkadang dalam penggunaan nyata, satu database `Redis` tidak cukup, dan sebuah proyek sering kali perlu mengkonfigurasi beberapa database. Pada saat ini, kita perlu memodifikasi file konfigurasi `redis.php` sebagai berikut:

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
    // Tambahkan connection pool Redis bernama 'foo'
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

### Menggunakan Proxy Class

Kita dapat menulis ulang kelas `FooRedis` dan mewarisi kelas `Hyperf\Redis\Redis`, lalu mengubah `poolName` menjadi `foo` seperti di atas, yang akan menyelesaikan pergantian connection pool. Contoh:

```php
<?php
use Hyperf\Redis\Redis;

class FooRedis extends Redis
{
    // Nilai key dari Pool yang sesuai
    protected $poolName = 'foo';
}

// Dapatkan kelas saat ini melalui container DI atau injeksi langsung
$redis = $this->container->get(FooRedis::class);

$result = $redis->keys('*');
```

### Menggunakan Factory Class

Ketika setiap database sesuai dengan skenario penggunaan yang tetap, menggunakan proxy class adalah cara yang baik untuk membedakannya. Namun terkadang kebutuhan bisa lebih dinamis. Dalam kasus ini, kita dapat menggunakan factory class `Hyperf\Redis\RedisFactory` untuk secara dinamis melewatkan `poolName` guna mendapatkan client dari connection pool yang sesuai, tanpa perlu membuat proxy class untuk setiap database. Contoh:

```php
<?php
use Hyperf\Redis\RedisFactory;
use Hyperf\Context\ApplicationContext;

$container = ApplicationContext::getContainer();

// Dapatkan kelas RedisFactory melalui container DI atau injeksi langsung
$redis = $container->get(RedisFactory::class)->get('foo');
$result = $redis->keys('*');
```

## Sentinel Mode

Untuk mengaktifkan Sentinel mode, ubah konfigurasi di file `.env` atau `redis.php` sebagai berikut:

Pisahkan beberapa node sentinel dengan `;`

```env
REDIS_HOST=
REDIS_AUTH=Password instance Redis
REDIS_PORT=
REDIS_DB=
REDIS_SENTINEL_ENABLE=true
REDIS_SENTINEL_PASSWORD=Password sentinel Redis
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

### Menggunakan `name`

Konfigurasi `cluster`, modifikasi `redis.ini`, atau Anda juga dapat memodifikasi `Dockerfile` sebagai berikut:

```shell
    # - konfigurasi PHP
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
// Konfigurasi lainnya diabaikan
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

### Menggunakan `seeds`

Tentu saja, Anda juga dapat langsung menggunakan `seeds` tanpa mengkonfigurasi `name`. Sebagai berikut:

```php
<?php
// Konfigurasi lainnya diabaikan
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

Pengguna dapat memodifikasi `options` untuk mengatur opsi konfigurasi `Redis`.

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
            // atau 'serializer' => \Redis::SERIALIZER_PHP, untuk v3.0.38 atau lebih tinggi
        ],
    ],
];
```

Sebagai contoh, mengatur `Redis` agar tidak pernah timeout:

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
            // atau 'read_timeout' => -1, untuk v3.1.3 atau lebih tinggi
        ],
    ],
];
```

> Untuk beberapa versi ekstensi `phpredis`, `value` dari `option` harus bertipe `string`.
