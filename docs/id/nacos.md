# Nacos

Sebuah client coroutine `PHP` untuk `Nacos`, terintegrasi secara sempurna
dengan configuration center dan microservice governance dari `Hyperf`.

## Instalasi

```shell
composer require hyperf/nacos
```

### Publikasikan file konfigurasi

```shell
php bin/hyperf.php vendor:publish hyperf/nacos
```

```php
<?php

declare(strict_types=1);

return [
    // Developers who cannot use the IP port form can directly configure the url
    // 'url' => '',
    'host' => '127.0.0.1',
    'port' => 8848,
    'username' => null,
    'password' => null,
    'guzzle' => [
        'config' => null,
    ],
];

```

## Service dan instans

Komponen saat ini masih mempertahankan fungsionalitas registrasi service
yang disediakan sebelumnya.

Cukup instal komponen `hyperf/service-governance-nacos`, lalu konfigurasikan
listener dan proses kustom berikut.

`Hyperf\ServiceGovernanceNacos\Listener\MainWorkerStartListener`
`Hyperf\ServiceGovernanceNacos\Listener\OnShutdownListener`
`Hyperf\ServiceGovernanceNacos\Process\InstanceBeatProcess`

Kemudian tambahkan konfigurasi berikut untuk mendengarkan event `Shutdown`

- config/autoload/server.php

```php
<?php
use Hyperf\Server\Event;
return [
    // ...other
    'callbacks' => [
        // ...other
        Event::ON_SHUTDOWN => [Hyperf\Framework\Bootstrap\ShutdownCallback::class, 'onShutdown']
    ]
];
```

## Autentikasi Layanan Aliyun

Saat menggunakan layanan Nacos Aliyun, Anda mungkin perlu menggunakan
autentikasi AK dan SK. Komponen Nacos mendukung hal ini secara bawaan
(natively). Kita dapat dengan mudah menambahkan konfigurasi yang sesuai sebagai
berikut:

```php
<?php

declare(strict_types=1);

return [
    // nacos server url like https://nacos.hyperf.io, Priority is higher than host:port
    // 'uri' => 'http://127.0.0.1:8848/',
    // The nacos host info
    'host' => '127.0.0.1',
    'port' => 8848,
    // The nacos account info
    'username' => null,
    'password' => null,
    'access_key' => 'xxxx',
    'access_secret' => 'yyyy',
    'guzzle' => [
        'config' => null,
    ],
];
```
