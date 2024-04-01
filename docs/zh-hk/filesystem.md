# 文件系統

文件系統組件集成了 PHP 生態中大名鼎鼎的 `League\Flysystem` (這也是 Laravel 等諸多知名框架的底層庫)。通過合理抽象，程序不必感知存儲引擎究竟是本地硬盤還是雲服務器，實現解耦。本組件對常用的雲存儲服務提供了協程化支持。

## 安裝

```shell
composer require hyperf/filesystem
```

`League\Flysystem` 組件 `v1.0`, `v2.0` 和 `v3.0` 版本變動較大，所以需要根據不同的版本，安裝對應的適配器

- 阿里雲 OSS 適配器

`Flysystem v1.0` 版本

```shell
composer require xxtime/flysystem-aliyun-oss
```

`Flysystem v2.0` 和 `Flysystem v3.0` 版本

```shell
composer require hyperf/flysystem-oss
```

- S3 適配器

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

- 七牛適配器

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

- 內存適配器

`Flysystem v1.0` 版本

```shell
composer require "league/flysystem-memory:^1.0"
```

`Flysystem v2.0` 版本

```shell
composer require "league/flysystem-memory:^2.0"
```

- 騰訊雲 COS 適配器

`Flysystem v1.0` 版本

> flysystem-cos v2.0 版本已經不推薦使用了，請按照最新的文檔修改為 3.0 版本

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

安裝完成後，執行

```bash
php bin/hyperf.php vendor:publish hyperf/filesystem
```

就會生成 `config/autoload/file.php` 文件。在該文件中設置默認驅動，並配置對應驅動的 access key、access secret 等信息就可以使用了。

## 使用

通過 DI 注入 `League\Flysystem\Filesystem` 即可使用。

API 如下：

> 以下示例為 Flysystem v1.0 版本，v2.0 版本請看官方文檔

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

在某些時候，您會需要同時使用多種存儲媒介。這時可以注入 `Hyperf\Filesystem\FilesystemFactory` 來動態選擇使用哪種驅動。

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

### 配置靜態資源

如果您希望通過 http 訪問上傳到本地的文件，請在 `config/autoload/server.php` 配置中增加以下配置。

```php
return [
    'settings' => [
        ...
        // 將 public 替換為上傳目錄
        'document_root' => BASE_PATH . '/public',
        'enable_static_handler' => true,
    ],
];

```

## 注意事項

1. S3 存儲請確認安裝 `hyperf/guzzle` 組件以提供協程化支持。阿里雲、七牛雲、騰訊云云存儲請[開啓 Curl Hook](/zh-hk/coroutine?id=swoole-runtime-hook-level)來使用協程。因 Curl Hook 的參數支持性問題，請使用 Swoole 4.4.13 以上版本。
2. minIO, ceph radosgw 等私有對象存儲方案均支持 S3 協議，可以使用 S3 適配器。
3. 使用 Local 驅動時，根目錄是配置好的地址，而不是操作系統的根目錄。例如，Local 驅動 `root` 設置為 `/var/www`, 則本地磁盤上的 `/var/www/public/file.txt` 通過 flysystem API 訪問時應使用 `/public/file.txt` 或 `public/file.txt` 。
4. 以阿里雲 OSS 為例，1 核 1 進程讀操作性能對比：

```bash
ab -k -c 10 -n 1000 http://127.0.0.1:9501/
```

未開啓 CURL HOOK：

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

開啓 CURL HOOK 後：

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

## 詳細配置

```php
return [
    // 選擇storage下對應驅動的鍵即可。
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
            // 可選，如果 bucket 為私有訪問請打開此項
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
