# Phar packager

## Instalasi

```bash
composer require hyperf/phar
```

## Penggunaan

- Dikemas secara default

```shell
php bin/hyperf.php phar:build
```

- Mengatur nama package

```shell
php bin/hyperf.php phar:build --name=your_project.phar
```

- Mengatur versi package

```shell
php bin/hyperf.php phar:build --phar-version=1.0.1
```

- Mengatur file startup

```shell
php bin/hyperf.php phar:build --bin=bin/hyperf.php
```

- Mengatur direktori pengemasan (packaging)

```shell
php bin/hyperf.php phar:build --path=BASE_PATH
```

- Memetakan file eksternal

> Membutuhkan hyperf/phar versi >= v2.1.7

Perintah berikut memungkinkan package `phar` untuk membaca file `.env` di
direktori yang sama, sehingga `phar` dapat didistribusikan ke berbagai
lingkungan (environment).

```shell
php bin/hyperf.php phar:build -M .env
```

## Menjalankan

```shell
php your_project.phar start
```

## Perhatian

Setelah dikemas, aplikasi berjalan dalam bentuk package `phar`, yang berbeda
dengan berjalan dalam mode source code. Direktori `runtime` di dalam package
`phar` tidak dapat ditulis (not writable).
Oleh karena itu, kita perlu melakukan override beberapa lokasi direktori yang
dapat ditulis.

> Ubah sesuai dengan situasi yang sebenarnya

- pid_file

Ubah konfigurasi `server.php`.

```php
<?php

return [
     'settings' => [
         'pid_file' => '/tmp/runtime/hyperf.pid',
     ],
];
```

- logger

Ubah konfigurasi `logger.php`

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

Phar packager akan secara otomatis mengatur `scan_cacheable` menjadi `true` di
dalam konfigurasi `config.php`.

Tentu saja, Anda juga dapat secara aktif mengubah konfigurasi ini menjadi
`true`.
