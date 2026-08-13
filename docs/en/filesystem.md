# File System

The file system component integrates the well-known `League\Flysystem` in the PHP ecosystem (which is also the underlying library for many well-known frameworks like Laravel). Through reasonable abstraction, the program does not need to perceive whether the storage engine is a local hard disk or a cloud server, achieving decoupling. This component provides coroutine support for commonly used cloud storage services.

## Installation

```shell
composer require hyperf/filesystem
```

The version changes of the `League\Flysystem` component `v1.0`, `v2.0`, and `v3.0` are quite large, so you need to install the corresponding adapter based on different versions.

- Aliyun OSS Adapter

`Flysystem v1.0` version

```shell
composer require xxtime/flysystem-aliyun-oss
```

`Flysystem v2.0` and `Flysystem v3.0` versions

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

> flysystem-cos v2.0 version is no longer recommended, please modify to version 3.0 according to the latest documentation.

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

After the installation is complete, execute:

```bash
php bin/hyperf.php vendor:publish hyperf/filesystem
```

This will generate the `config/autoload/file.php` file. In this file, set the default driver and configure the access key, access secret, etc., of the corresponding driver to use it.

## Usage

You can use it by injecting `League\Flysystem\Filesystem` via DI.

The API is as follows:

> The following example is for Flysystem v1.0 version. For v2.0 version, please refer to the official documentation.

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

Sometimes, you will need to use multiple storage media at the same time. In this case, you can inject `Hyperf\Filesystem\FilesystemFactory` to dynamically select which driver to use.

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

### Configure Static Resources

If you want to access files uploaded to the local disk via http, please add the following configuration to your `config/autoload/server.php` configuration.

```php
return [
    'settings' => [
        ...
        // Replace public with your upload directory
        'document_root' => BASE_PATH . '/public',
        'enable_static_handler' => true,
    ],
];
```

## Notes

1. For S3 storage, please make sure to install the `hyperf/guzzle` component to provide coroutine support. For Aliyun OSS, Qiniu Cloud, and Tencent Cloud storage, please [enable Curl Hook](/en/coroutine?id=swoole-runtime-hook-level) to use coroutines. Due to Curl Hook's parameter support issues, please use Swoole version 4.4.13 or higher.
2. Private object storage solutions such as minIO and ceph radosgw all support the S3 protocol and can use the S3 adapter.
3. When using the Local driver, the root directory is the configured address, not the root directory of the operating system. For example, if the Local driver `root` is set to `/var/www`, then `/var/www/public/file.txt` on the local disk should be accessed via `/public/file.txt` or `public/file.txt` when accessed via the flysystem API.
4. Taking Aliyun OSS as an example, read performance comparison for 1 core 1 process:

```bash
ab -k -c 10 -n 1000 http://127.0.0.1:9501/
```

Before enabling CURL HOOK:

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

After enabling CURL HOOK:

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

## Detailed Configuration

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
            // Optional, please turn on if the bucket is for private access
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
