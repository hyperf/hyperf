# 文件系统

文件系统组件集成了 PHP 生态中大名鼎鼎的 `League\Flysystem` (这也是 Laravel 等诸多知名框架的底层库)。通过合理抽象，程序不必感知存储引擎究竟是本地硬盘还是云服务器，实现解耦。本组件对常用的云存储服务提供了协程化支持。

## 安装

```shell
composer require hyperf/filesystem
```

`League\Flysystem` 组件 `v1.0`, `v2.0` 和 `v3.0` 版本变动较大，所以需要根据不同的版本，安装对应的适配器

- 阿里云 OSS 适配器

`Flysystem v1.0` 版本

```shell
composer require xxtime/flysystem-aliyun-oss
```

`Flysystem v2.0` 和 `Flysystem v3.0` 版本

```shell
composer require hyperf/flysystem-oss
```

- S3 适配器

`Flysystem v1.0` 版本

```shell
composer require "league/flysystem-aws-s3-v3:^1.0"
composer require hyperf/guzzle
```

`Flysystem v2.0` 版本

```shell
composer require "league/flysystem-aws-s3-v3:^2.0"
composer require hyperf/guzzle
```

- 七牛适配器

`Flysystem v1.0` 版本

```shell
composer require "overtrue/flysystem-qiniu:^1.0"
```

`Flysystem v2.0` 版本

```shell
composer require "overtrue/flysystem-qiniu:^2.0"
```

`Flysystem v3.0` 版本

```shell
composer require "overtrue/flysystem-qiniu:^3.0"
```

- 内存适配器

`Flysystem v1.0` 版本

```shell
composer require "league/flysystem-memory:^1.0"
```

`Flysystem v2.0` 版本

```shell
composer require "league/flysystem-memory:^2.0"
```

- 腾讯云 COS 适配器

`Flysystem v1.0` 版本

> flysystem-cos v2.0 版本已经不推荐使用了，请按照最新的文档修改为 3.0 版本

```shell
composer require "overtrue/flysystem-cos:^3.0"
```

`Flysystem v2.0` 版本

```shell
composer require "overtrue/flysystem-cos:^4.0"
```

`Flysystem v3.0` 版本

```shell
composer require "overtrue/flysystem-cos:^5.0"
```

安装完成后，执行

```bash
php bin/hyperf.php vendor:publish hyperf/filesystem
```

就会生成 `config/autoload/file.php` 文件。在该文件中设置默认驱动，并配置对应驱动的 access key、access secret 等信息就可以使用了。

## 使用

通过 DI 注入 `League\Flysystem\Filesystem` 即可使用。

API 如下：

> 以下示例为 Flysystem v1.0 版本，v2.0 版本请看官方文档

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

在某些时候，您会需要同时使用多种存储媒介。这时可以注入 `Hyperf\Filesystem\FilesystemFactory` 来动态选择使用哪种驱动。

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

### 配置静态资源

如果您希望通过 http 访问上传到本地的文件，请在 `config/autoload/server.php` 配置中增加以下配置。

```php
return [
    'settings' => [
        ...
        // 将 public 替换为上传目录
        'document_root' => BASE_PATH . '/public',
        'enable_static_handler' => true,
    ],
];

```

## 注意事项

1. S3 存储请确认安装 `hyperf/guzzle` 组件以提供协程化支持。阿里云、七牛云、腾讯云云存储请[开启 Curl Hook](/zh-cn/coroutine?id=swoole-runtime-hook-level)来使用协程。因 Curl Hook 的参数支持性问题，请使用 Swoole 4.4.13 以上版本。
2. minIO, ceph radosgw 等私有对象存储方案均支持 S3 协议，可以使用 S3 适配器。
3. 使用 Local 驱动时，根目录是配置好的地址，而不是操作系统的根目录。例如，Local 驱动 `root` 设置为 `/var/www`, 则本地磁盘上的 `/var/www/public/file.txt` 通过 flysystem API 访问时应使用 `/public/file.txt` 或 `public/file.txt` 。
4. 以阿里云 OSS 为例，1 核 1 进程读操作性能对比：

```bash
ab -k -c 10 -n 1000 http://127.0.0.1:9501/
```

未开启 CURL HOOK：

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

开启 CURL HOOK 后：

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

## 详细配置

```php
return [
    // 选择storage下对应驱动的键即可。
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
            // overtrue/flysystem-cos ^2.0 配置如下
            'credentials' => [
                'appId' => env('COS_APPID'),
                'secretId' => env('COS_SECRET_ID'),
                'secretKey' => env('COS_SECRET_KEY'),
            ],
            // overtrue/flysystem-cos ^3.0 配置如下
            'app_id' => env('COS_APPID'),
            'secret_id' => env('COS_SECRET_ID'),
            'secret_key' => env('COS_SECRET_KEY'),
            // 可选，如果 bucket 为私有访问请打开此项
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
