# Filesystem

Komponen filesystem mengintegrasikan library populer `League\Flysystem` dalam
ekosistem PHP (ini juga merupakan library dasar dari banyak framework terkenal
seperti Laravel). Melalui abstraksi yang tepat, program tidak perlu mendeteksi
apakah mesin penyimpanan berupa hard disk lokal atau server cloud, sehingga
mencapai decoupling. Komponen ini menyediakan dukungan coroutine untuk layanan
cloud storage yang umum.

## Instalasi

```shell
composer require hyperf/filesystem
```

Versi komponen `League\Flysystem` `v1.0`, `v2.0`, dan `v3.0` telah mengalami
perubahan besar, sehingga Anda perlu menginstal adapter yang sesuai berdasarkan
versi yang berbeda.

- Adapter Alibaba Cloud OSS

Versi `Flysystem v1.0`

```shell
composer require xxtime/flysystem-aliyun-oss
```

Versi `Flysystem v2.0` dan `Flysystem v3.0`

```shell
composer require hyperf/flysystem-oss
```

- Adapter S3

Versi `Flysystem v1.0`

```shell
composer require "league/flysystem-aws-s3-v3:^1.0"
composer require hyperf/guzzle
```

Versi `Flysystem v2.0`

```shell
composer require "league/flysystem-aws-s3-v3:^2.0"
composer require hyperf/guzzle
```

- Adapter Qiniu

Versi `Flysystem v1.0`

```shell
composer require "overtrue/flysystem-qiniu:^1.0"
```

Versi `Flysystem v2.0`

```shell
composer require "overtrue/flysystem-qiniu:^2.0"
```

Versi `Flysystem v3.0`

```shell
composer require "overtrue/flysystem-qiniu:^3.0"
```

- Adapter Memory

Versi `Flysystem v1.0`

```shell
composer require "league/flysystem-memory:^1.0"
```

Versi `Flysystem v2.0`

```shell
composer require "league/flysystem-memory:^2.0"
```

- Adapter Tencent Cloud COS

Versi `Flysystem v1.0`

> flysystem-cos versi v2.0 telah usang (deprecated), silakan ubah ke versi 3.0
> sesuai dengan dokumentasi terbaru

```shell
composer require "overtrue/flysystem-cos:^3.0"
```

Versi `Flysystem v2.0`

```shell
composer require "overtrue/flysystem-cos:^4.0"
```

Versi `Flysystem v3.0`

```shell
composer require "overtrue/flysystem-cos:^5.0"
```

Setelah instalasi selesai, jalankan

```bash
php bin/hyperf.php vendor:publish hyperf/filesystem
```

File `config/autoload/file.php` akan dibuat. Atur driver default di file ini,
dan konfigurasikan access key, access secret, serta informasi lainnya dari
driver yang sesuai, lalu Anda dapat menggunakannya.

## Penggunaan

Ini dapat digunakan dengan menginjeksikan `League\Flysystem\Filesystem` melalui DI.

API-nya adalah sebagai berikut:

> Contoh berikut adalah untuk Flysystem versi v1.0, silakan merujuk ke
> dokumentasi resmi untuk versi v2.0

```php
<?php

declare(strict_types=1);

namespace App\Controller;

class IndexController extends AbstractController
{
    public function example(\League\Flysystem\Filesystem $filesystem)
    {
        // Process Upload
        $file = $this->request->file('upload');
        $stream = fopen($file->getRealPath(), 'r+');
        $filesystem->writeStream(
            'uploads/'.$file->getClientFilename(),
            $stream
        );
        fclose($stream);
        
        // Write Files
        $filesystem->write('path/to/file.txt', 'contents');

        // Add local file
        $stream = fopen('local/path/to/file.txt', 'r+');
        $result = $filesystem->writeStream('path/to/file.txt', $stream);
        if (is_resource($stream)) {
            fclose($stream);
        }

        // Update Files
        $filesystem->update('path/to/file.txt', 'new contents');

        // Check if a file exists
        $exists = $filesystem->has('path/to/file.txt');

        // Read Files
        $contents = $filesystem->read('path/to/file.txt');

        // Delete Files
        $filesystem->delete('path/to/file.txt');

        // Rename Files
        $filesystem->rename('filename.txt', 'newname.txt');

        // Copy Files
        $filesystem->copy('filename.txt', 'duplicate.txt');

        // list the contents
        $filesystem->listContents('path', false);
    }
}
```

Pada beberapa kasus, Anda mungkin perlu menggunakan beberapa media penyimpanan
sekaligus. Untuk melakukannya, Anda dapat menginjeksikan
`Hyperf\Filesystem\FilesystemFactory` untuk memilih driver yang akan digunakan
secara dinamis.

```php
<?php

declare(strict_types=1);

namespace App\Controller;

class IndexController
{
    public function example(\Hyperf\Filesystem\FilesystemFactory $factory)
    {
    	$local = $factory->get('local');
        // Write Files
        $local->write('path/to/file.txt', 'contents');

        $s3 = $factory->get('s3');

        $s3->write('path/to/file.txt', 'contents');
    }
}
```

### Mengonfigurasi Resource Statis

Jika Anda ingin mengakses file yang diunggah secara lokal melalui HTTP, silakan
tambahkan konfigurasi berikut ke konfigurasi `config/autoload/server.php`.

```php
return [
    'settings' => [
        ...
        // Replace public with the upload directory
        'document_root' => BASE_PATH . '/public',
        'enable_static_handler' => true,
    ],
];

```

## Catatan Penting

1. Pastikan untuk menginstal komponen `hyperf/guzzle` untuk penyimpanan S3 guna
   menyediakan dukungan coroutine. Untuk Alibaba Cloud, Qiniu Cloud, dan Tencent
   Cloud, silakan [Aktifkan Curl Hook](/id/coroutine?id=swoole-runtime-hook-level)
   untuk menggunakan coroutine. Karena dukungan parameter dari Curl Hook,
   silakan gunakan Swoole 4.4.13 atau versi yang lebih baru.
2. Solusi private object storage seperti minIO dan ceph radosgw semuanya
   mendukung protokol S3 dan dapat menggunakan adapter S3.
3. Saat menggunakan driver Local, direktori root-nya adalah alamat yang
   dikonfigurasi, bukan direktori root dari sistem operasi. Sebagai contoh, jika
   driver local `root` diatur ke `/var/www`, maka `/var/www/public/file.txt` pada
   disk lokal harus diakses melalui API flysystem menggunakan
   `/public/file.txt` atau `public/file.txt`.
4. Mengambil Alibaba Cloud OSS sebagai contoh, perbandingan performa operasi
   pembacaan pada 1 core dan 1 proses:

```bash
ab -k -c 10 -n 1000 http://127.0.0.1:9501/
```

CURL HOOK tidak aktif:

```
Concurrency Level:      10
Time taken for tests:   202.902 seconds
Complete requests:      1000
Failed requests:        0
Keep-Alive requests:    1000
Total transferred:      146000 bytes
HTML transferred:       5000 bytes
Requests per second:    4.93 [#/sec] (mean)
Time per request:       2029.016 [ms] (mean)
Time per request:       202.902 [ms] (mean, across all concurrent requests)
Transfer rate:          0.70 [Kbytes/sec] received
```

Setelah mengaktifkan CURL HOOK:

```
Concurrency Level:      10
Time taken for tests:   9.252 seconds
Complete requests:      1000
Failed requests:        0
Keep-Alive requests:    1000
Total transferred:      146000 bytes
HTML transferred:       5000 bytes
Requests per second:    108.09 [#/sec] (mean)
Time per request:       92.515 [ms] (mean)
Time per request:       9.252 [ms] (mean, across all concurrent requests)
Transfer rate:          15.41 [Kbytes/sec] received
```

## Detail Konfigurasi

```php
return [
    // Select the key corresponding to the driver under storage.
    'default' => 'local',
    'storage' => [
        'local' => [
            'driver' => \Hyperf\Filesystem\Adapter\LocalAdapterFactory::class,
            'root' => __DIR__ . '/../../runtime',
        ],
        'ftp' => [
            'driver' => \Hyperf\Filesystem\Adapter\FtpAdapterFactory::class,
            'host' => 'ftp.example.com',
            'username' => 'username',
            'password' => 'password',

            /* optional config settings */
            'port' => 21,
            'root' => '/path/to/root',
            'passive' => true,
            'ssl' => true,
            'timeout' => 30,
            'ignorePassiveAddress' => false,
        ],
        'memory' => [
            'driver' => \Hyperf\Filesystem\Adapter\MemoryAdapterFactory::class,
        ],
        's3' => [
            'driver' => \Hyperf\Filesystem\Adapter\S3AdapterFactory::class,
            'credentials' => [
                'key' => env('S3_KEY'),
                'secret' => env('S3_SECRET'),
            ],
            'region' => env('S3_REGION'),
            'version' => 'latest',
            'bucket_endpoint' => false,
            'use_path_style_endpoint' => false,
            'endpoint' => env('S3_ENDPOINT'),
            'bucket_name' => env('S3_BUCKET'),
        ],
        'minio' => [
            'driver' => \Hyperf\Filesystem\Adapter\S3AdapterFactory::class,
            'credentials' => [
                'key' => env('S3_KEY'),
                'secret' => env('S3_SECRET'),
            ],
            'region' => env('S3_REGION'),
            'version' => 'latest',
            'bucket_endpoint' => false,
            'use_path_style_endpoint' => true,
            'endpoint' => env('S3_ENDPOINT'),
            'bucket_name' => env('S3_BUCKET'),
        ],
        'oss' => [
            'driver' => \Hyperf\Filesystem\Adapter\AliyunOssAdapterFactory::class,
            'accessId' => env('OSS_ACCESS_ID'),
            'accessSecret' => env('OSS_ACCESS_SECRET'),
            'bucket' => env('OSS_BUCKET'),
            'endpoint' => env('OSS_ENDPOINT'),
            // 'timeout'        => 3600,
            // 'connectTimeout' => 10,
            // 'isCName'        => false,
            // 'token'          => '',
        ],
        'qiniu' => [
            'driver' => \Hyperf\Filesystem\Adapter\QiniuAdapterFactory::class,
            'accessKey' => env('QINIU_ACCESS_KEY'),
            'secretKey' => env('QINIU_SECRET_KEY'),
            'bucket' => env('QINIU_BUCKET'),
            'domain' => env('QINIU_DOMAIN'),
        ],
        'cos' => [
            'driver' => \Hyperf\Filesystem\Adapter\CosAdapterFactory::class,
            'region' => env('COS_REGION'),
            // overtrue/flysystem-cos ^2.0 The configuration is as follows
            'credentials' => [
                'appId' => env('COS_APPID'),
                'secretId' => env('COS_SECRET_ID'),
                'secretKey' => env('COS_SECRET_KEY'),
            ],
            // overtrue/flysystem-cos ^3.0 The configuration is as follows
            'app_id' => env('COS_APPID'),
            'secret_id' => env('COS_SECRET_ID'),
            'secret_key' => env('COS_SECRET_KEY'),
            // Optional, please turn this on if the bucket has private access
            // 'signed_url' => false,
            'bucket' => env('COS_BUCKET'),
            'read_from_cdn' => false,
            // 'timeout'         => 60,
            // 'connect_timeout' => 60,
            // 'cdn'             => '',
            // 'scheme'          => 'https',
        ],
    ],
];
```
