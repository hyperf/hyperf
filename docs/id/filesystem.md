# File System

Komponen filesystem mengintegrasikan `League\Flysystem` yang terkenal di ekosistem PHP (yang juga merupakan library dasar untuk banyak framework terkenal seperti Laravel). Melalui abstraksi yang tepat, program tidak perlu mengetahui apakah storage engine adalah hard disk lokal atau server cloud, sehingga mencapai decoupling. Komponen ini menyediakan dukungan coroutine untuk layanan cloud storage yang umum digunakan.

## Instalasi

```shell
composer require hyperf/filesystem
```

Perubahan versi dari komponen `League\Flysystem` `v1.0`, `v2.0`, dan `v3.0` cukup besar, jadi Anda perlu menginstal adapter yang sesuai berdasarkan versi yang berbeda.

- Aliyun OSS Adapter

`Flysystem v1.0` version

```shell
composer require xxtime/flysystem-aliyun-oss
```

`Flysystem v2.0` dan `Flysystem v3.0` version

```shell
composer require hyperf/flysystem-oss
```

- S3 Adapter

`Flysystem v1.0` version

```shell
composer require "league/flysystem-aws-s3-v3:^1.0"
composer require hyperf/guzzle
```

`Flysystem v2.0` version

```shell
composer require "league/flysystem-aws-s3-v3:^2.0"
composer require hyperf/guzzle
```

- Qiniu Adapter

`Flysystem v1.0` version

```shell
composer require "overtrue/flysystem-qiniu:^1.0"
```

`Flysystem v2.0` version

```shell
composer require "overtrue/flysystem-qiniu:^2.0"
```

`Flysystem v3.0` version

```shell
composer require "overtrue/flysystem-qiniu:^3.0"
```

- Memory Adapter

`Flysystem v1.0` version

```shell
composer require "league/flysystem-memory:^1.0"
```

`Flysystem v2.0` version

```shell
composer require "league/flysystem-memory:^2.0"
```

- Tencent Cloud COS Adapter

`Flysystem v1.0` version

> flysystem-cos v2.0 tidak lagi direkomendasikan, silakan ubah ke versi 3.0 sesuai dokumentasi terbaru.

```shell
composer require "overtrue/flysystem-cos:^3.0"
```

`Flysystem v2.0` version

```shell
composer require "overtrue/flysystem-cos:^4.0"
```

`Flysystem v3.0` version

```shell
composer require "overtrue/flysystem-cos:^5.0"
```

Setelah instalasi selesai, jalankan:

```bash
php bin/hyperf.php vendor:publish hyperf/filesystem
```

Ini akan menghasilkan file `config/autoload/file.php`. Di file ini, atur driver default dan konfigurasikan access key, access secret, dll., dari driver yang sesuai untuk menggunakannya.

## Penggunaan

Anda dapat menggunakannya dengan menginjeksi `League\Flysystem\Filesystem` melalui DI.

API-nya adalah sebagai berikut:

> Contoh berikut untuk Flysystem v1.0. Untuk v2.0, silakan merujuk ke dokumentasi resmi.

```php
<?php

declare(strict_types=1);

namespace App\Controller;

class IndexController extends AbstractController
{
    public function example(\League\Flysystem\Filesystem $filesystem)
    {
        // Memproses Upload
        $file = $this->request->file('upload');
        $stream = fopen($file->getRealPath(), 'r+');
        $filesystem->writeStream(
            'uploads/'.$file->getClientFilename(),
            $stream
        );
        fclose($stream);
        
        // Menulis Files
        $filesystem->write('path/to/file.txt', 'contents');

        // Menambahkan file lokal
        $stream = fopen('local/path/to/file.txt', 'r+');
        $result = $filesystem->writeStream('path/to/file.txt', $stream);
        if (is_resource($stream)) {
            fclose($stream);
        }

        // Memperbarui Files
        $filesystem->update('path/to/file.txt', 'new contents');

        // Memeriksa apakah file ada
        $exists = $filesystem->has('path/to/file.txt');

        // Membaca Files
        $contents = $filesystem->read('path/to/file.txt');

        // Menghapus Files
        $filesystem->delete('path/to/file.txt');

        // Mengganti Nama Files
        $filesystem->rename('filename.txt', 'newname.txt');

        // Menyalin Files
        $filesystem->copy('filename.txt', 'duplicate.txt');

        // Mendaftar konten
        $filesystem->listContents('path', false);
    }
}
```

Terkadang, Anda perlu menggunakan beberapa media penyimpanan secara bersamaan. Dalam kasus ini, Anda dapat menginjeksi `Hyperf\Filesystem\FilesystemFactory` untuk secara dinamis memilih driver mana yang akan digunakan.

```php
<?php

declare(strict_types=1);

namespace App\Controller;

class IndexController
{
    public function example(\Hyperf\Filesystem\FilesystemFactory $factory)
    {
    	$local = $factory->get('local');
        // Menulis Files
        $local->write('path/to/file.txt', 'contents');

        $s3 = $factory->get('s3');

        $s3->write('path/to/file.txt', 'contents');
    }
}
```

### Mengonfigurasi Static Resources

Jika Anda ingin mengakses file yang diunggah ke disk lokal melalui http, harap tambahkan konfigurasi berikut ke dalam konfigurasi `config/autoload/server.php` Anda.

```php
return [
    'settings' => [
        ...
        // Ganti public dengan direktori upload Anda
        'document_root' => BASE_PATH . '/public',
        'enable_static_handler' => true,
    ],
];
```

## Catatan

1. Untuk S3 storage, pastikan untuk menginstal komponen `hyperf/guzzle` untuk menyediakan dukungan coroutine. Untuk Aliyun OSS, Qiniu Cloud, dan Tencent Cloud storage, harap [aktifkan Curl Hook](/id/coroutine?id=swoole-runtime-hook-level) untuk menggunakan coroutine. Karena masalah dukungan parameter Curl Hook, gunakan Swoole versi 4.4.13 atau lebih tinggi.
2. Solusi object storage privat seperti minIO dan ceph radosgw semuanya mendukung protokol S3 dan dapat menggunakan S3 adapter.
3. Saat menggunakan Local driver, root directory adalah alamat yang dikonfigurasi, bukan root directory sistem operasi. Misalnya, jika Local driver `root` diatur ke `/var/www`, maka `/var/www/public/file.txt` di disk lokal harus diakses melalui `/public/file.txt` atau `public/file.txt` saat diakses melalui flysystem API.
4. Mengambil Aliyun OSS sebagai contoh, perbandingan performa baca untuk 1 core 1 process:

```bash
ab -k -c 10 -n 1000 http://127.0.0.1:9501/
```

Sebelum mengaktifkan CURL HOOK:

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

## Konfigurasi Detail

```php
return [
    // Pilih key yang sesuai dengan driver di bawah storage.
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

            /* pengaturan konfigurasi opsional */
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
            // overtrue/flysystem-cos ^2.0 configuration as follows
            'credentials' => [
                'appId' => env('COS_APPID'),
                'secretId' => env('COS_SECRET_ID'),
                'secretKey' => env('COS_SECRET_KEY'),
            ],
            // overtrue/flysystem-cos ^3.0 configuration as follows
            'app_id' => env('COS_APPID'),
            'secret_id' => env('COS_SECRET_ID'),
            'secret_key' => env('COS_SECRET_KEY'),
            // Opsional, harap aktifkan jika bucket untuk akses privat
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
