# Phar Packager

## Instalasi

```bash
composer require hyperf/phar
```

## Penggunaan

- Packaging default

```shell
php bin/hyperf.php phar:build
```

- Tentukan nama paket

```shell
php bin/hyperf.php phar:build --name=your_project.phar
```

- Tentukan versi paket

```shell
php bin/hyperf.php phar:build --phar-version=1.0.1
```

- Tentukan file startup

```shell
php bin/hyperf.php phar:build --bin=bin/hyperf.php
```

- Tentukan direktori packaging

```shell
php bin/hyperf.php phar:build --path=BASE_PATH
```

- Petakan file eksternal

> Memerlukan hyperf/phar versi >= v2.1.7

Perintah berikut memungkinkan paket `phar` membaca file `.env` di direktori yang sama, yang memudahkan distribusi `phar` ke berbagai environment.

```shell
php bin/hyperf.php phar:build -M .env
```

## Menjalankan

```shell
php your_project.phar start
```

## Hal yang Perlu Diperhatikan

Setelah packaging, aplikasi berjalan sebagai paket `phar`. Berbeda dengan mode source code, direktori `runtime` di dalam paket `phar` tidak bisa ditulisi, jadi kita perlu mengubah beberapa lokasi direktori yang bisa ditulisi.

> Sesuaikan dengan kondisi aktual.

- pid_file

Ubah konfigurasi `server.php`:

```php
<?php

return [
    'settings' => [
        'pid_file' => '/tmp/runtime/hyperf.pid',
    ],
];
```

- logger

Ubah konfigurasi `logger.php`:

```php
<?php
return [
    'default' => [
        'handler' => [
            'class' => Monolog\Handler\StreamHandler::class,
            'constructor' => [
                'stream' => '/tmp/runtime/logs/hyperf.log',
                'level' => Monolog\Logger::INFO,
            ],
        ],
    ],
];
```

- scan_cacheable

Phar packager akan secara aktif mengatur `scan_cacheable` dalam konfigurasi `config.php` menjadi `true`.

Tentu saja, Anda juga dapat secara aktif mengubah konfigurasi ini menjadi `true`.
