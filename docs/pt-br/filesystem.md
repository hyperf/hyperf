# Sistema de arquivos

O componente de sistema de arquivos integra o famoso `League\Flysystem` do ecossistema PHP (que também é a biblioteca de base de muitos frameworks conhecidos, como o Laravel). Através de uma abstração adequada, o programa não precisa perceber se o mecanismo de armazenamento é um disco local ou um servidor em nuvem, realizando o desacoplamento. Este componente fornece suporte a Coroutine para os serviços de armazenamento em nuvem mais comuns.

## Instalação

```shell
composer require hyperf/filesystem
```

As versões `v1.0`, `v2.0` e `v3.0` dos componentes do `League\Flysystem` mudaram bastante, então você precisa instalar os adapters correspondentes de acordo com as diferentes versões

- Adapter Alibaba Cloud OSS

Versão `Flysystem v1.0`

```shell
composer require xxtime/flysystem-aliyun-oss
```

Versões `Flysystem v2.0` e `Flysystem v3.0`

```shell
composer require hyperf/flysystem-oss
```

- Adapter S3

Versão `Flysystem v1.0`

```shell
composer require "league/flysystem-aws-s3-v3:^1.0"
composer require hyperf/guzzle
```

Versão `Flysystem v2.0`

```shell
composer require "league/flysystem-aws-s3-v3:^2.0"
composer require hyperf/guzzle
```

Versão `Flysystem v3.0`

```shell
composer require "league/flysystem-aws-s3-v3:^3.0"
composer require hyperf/guzzle
```

- Adapter Qiniu

Versão `Flysystem v1.0`

```shell
composer require "overtrue/flysystem-qiniu:^1.0"
```

Versão `Flysystem v2.0`

```shell
composer require "overtrue/flysystem-qiniu:^2.0"
```

Versão `Flysystem v3.0`

```shell
composer require "overtrue/flysystem-qiniu:^3.0"
```

- Adapter de memória

Versão `Flysystem v1.0`

```shell
composer require "league/flysystem-memory:^1.0"
```

Versão `Flysystem v2.0`

```shell
composer require "league/flysystem-memory:^2.0"
```

- Adapter Tencent Cloud COS

Versão `Flysystem v1.0`

> A versão flysystem-cos v2.0 está descontinuada; modifique-a para a versão 3.0 de acordo com a documentação mais recente

```shell
composer require "overtrue/flysystem-cos:^3.0"
```

Versão `Flysystem v2.0`

```shell
composer require "overtrue/flysystem-cos:^4.0"
```

Versão `Flysystem v3.0`

```shell
composer require "overtrue/flysystem-cos:^5.0"
```

Após a conclusão da instalação, execute

```bash
php bin/hyperf.php vendor:publish hyperf/filesystem
```

O arquivo `config/autoload/file.php` será gerado. Defina o driver padrão nesse arquivo e configure a access key, o access secret e outras informações do driver correspondente, e você poderá usá-lo.

## Uso

Ele pode ser usado injetando `League\Flysystem\Filesystem` através de DI.

A API é a seguinte:

> O exemplo a seguir é da versão Flysystem v1.0; para a versão v2.0, consulte a documentação oficial

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

Em algum momento, você precisará usar múltiplos mídias de armazenamento ao mesmo tempo. Nesse caso, você pode injetar `Hyperf\Filesystem\FilesystemFactory` para escolher dinamicamente qual driver usar.

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

### Configurar recursos estáticos

Se você quiser acessar arquivos enviados localmente via http, adicione a seguinte configuração em `config/autoload/server.php`.

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

## Observações

1. Certifique-se de instalar o componente `hyperf/guzzle` para o armazenamento S3, a fim de fornecer suporte a Coroutine. Para Alibaba Cloud, Qiniu Cloud e Tencent Cloud, [habilite o Curl Hook](/pt-br/coroutine?id=swoole-runtime-hook-level) para usar Coroutines. Devido ao suporte de parâmetros do Curl Hook, use Swoole 4.4.13 ou posterior.
2. Soluções de armazenamento de objetos privados, como minIO e ceph radosgw, suportam o protocolo S3 e podem usar o adapter S3.
3. Ao usar o driver Local, o diretório raiz é o endereço configurado, não o diretório raiz do sistema operacional. Por exemplo, se o `root` do driver local estiver configurado como `/var/www`, então `/var/www/public/file.txt` no disco local deve ser acessado através da API do flysystem usando `/public/file.txt` ou `public/file.txt`.
4. Tomando o Alibaba Cloud OSS como exemplo, a comparação de desempenho da operação de leitura com 1 core e 1 processo:

```bash
ab -k -c 10 -n 1000 http://127.0.0.1:9501/
```

CURL HOOK não habilitado:

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

Depois de habilitar o CURL HOOK:

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

## Configuração detalhada

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
