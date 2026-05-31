# Nacos

Coroutine client `PHP` untuk `Nacos`, terintegrasi sempurna dengan configuration center dan microservice governance Hyperf.

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
    // Developer yang tidak bisa menggunakan format IP port dapat langsung mengkonfigurasi url
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

## Services dan Instances

Komponen saat ini masih mempertahankan fungsi registrasi service yang disediakan sebelumnya.

Anda hanya perlu menginstal komponen `hyperf/service-governance-nacos`, lalu konfigurasikan listener dan custom process berikut.

`Hyperf\ServiceGovernanceNacos\Listener\MainWorkerStartListener`
`Hyperf\ServiceGovernanceNacos\Listener\OnShutdownListener`
`Hyperf\ServiceGovernanceNacos\Process\InstanceBeatProcess`

Kemudian tambahkan konfigurasi berikut untuk mendengarkan event `Shutdown`:

- config/autoload/server.php

```php
<?php
use Hyperf\Server\Event;
return [
    // ...lainnya
    'callbacks' => [
        // ...lainnya
        Event::ON_SHUTDOWN => [Hyperf\Framework\Bootstrap\ShutdownCallback::class, 'onShutdown']
    ]
];
```

## Alibaba Cloud Service Authentication

Saat menggunakan layanan Nacos dari Alibaba Cloud, Anda mungkin perlu menggunakan AK dan SK untuk autentikasi. Komponen Nacos mendukungnya secara native. Kita dapat dengan mudah menambahkan konfigurasi yang sesuai sebagai berikut:

```php
<?php

declare(strict_types=1);

return [
    // url server nacos seperti https://nacos.hyperf.io, Prioritas lebih tinggi dari host:port
    // 'uri' => 'http://127.0.0.1:8848/',
    // Informasi host nacos
    'host' => '127.0.0.1',
    'port' => 8848,
    // Informasi akun nacos
    'username' => null,
    'password' => null,
    'access_key' => 'xxxx',
    'access_secret' => 'yyyy',
    'guzzle' => [
        'config' => null,
    ],
];
```
