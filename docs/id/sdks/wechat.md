# EasyWeChat

[EasyWeChat](https://www.easywechat.com/) adalah WeChat SDK open-source (bukan SDK resmi dari WeChat).

> Jika Anda menggunakan Swoole 4.7.0 atau lebih tinggi dengan opsi native curl yang diaktifkan, Anda tidak perlu mengikuti dokumentasi ini.

> Karena komponen ini menggunakan `Curl` secara default, kita perlu mengganti `GuzzleClient` dengan client yang mendukung coroutine, atau memodifikasi konstanta [SWOOLE_HOOK_FLAGS](id/coroutine.md#swoole-runtime-hook-level).

## Mengganti `Handler`

Mengambil contoh Official Account:

```php
<?php

use Hyperf\Context\ApplicationContext;
use EasyWeChat\Factory;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Hyperf\Guzzle\CoroutineHandler;

$container = ApplicationContext::getContainer();

$app = Factory::officialAccount($config);
$handler = new CoroutineHandler();

// Set HttpClient; beberapa interface menggunakan http_client secara langsung.
$config = $app['config']->get('http', []);
$config['handler'] = $stack = HandlerStack::create($handler);
$app->rebind('http_client', new Client($config));

// Beberapa interface me-reset Handler berdasarkan guzzle_handler saat meminta data.
$app['guzzle_handler'] = $handler;

// Jika Anda menggunakan OfficialAccount, Anda juga perlu mengatur parameter berikut:
$app->oauth->setGuzzleOptions([
    'http_errors' => false,
    'handler' => $stack,
]);
```

## Memodifikasi `SWOOLE_HOOK_FLAGS`

Lihat [SWOOLE_HOOK_FLAGS](id/coroutine.md#swoole-runtime-hook-level).

## Cara Menggunakan EasyWeChat

`EasyWeChat` dirancang untuk arsitektur `PHP-FPM`, sehingga memerlukan beberapa modifikasi agar dapat bekerja dengan baik di Hyperf. Di bawah ini, kita akan menggunakan callback pembayaran sebagai contoh.

1. `EasyWeChat` sudah menyertakan parsing `XML`, jadi kita hanya perlu mendapatkan `XML` mentahnya.

```php
$xml = $this->request->getBody()->getContents();
```

2. Masukkan data XML ke dalam objek `Request` dari `EasyWeChat`.

```php
<?php
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;

$get = $this->request->getQueryParams();
$post = $this->request->getParsedBody();
$cookie = $this->request->getCookieParams();
$uploadFiles = $this->request->getUploadedFiles() ?? [];
$server = $this->request->getServerParams();
$xml = $this->request->getBody()->getContents();
$files = [];
/** @var \Hyperf\HttpMessage\Upload\UploadedFile $v */
foreach ($uploadFiles as $k => $v) {
    $files[$k] = $v->toArray();
}
$request = new Request($get, $post, [], $cookie, $files, $server, $xml);
$request->headers = new HeaderBag($this->request->getHeaders());
$app->rebind('request', $request);
// Lakukan sesuatu...
```

3. Konfigurasi Server

Jika Anda perlu menggunakan fungsi konfigurasi server dari platform Official Account WeChat, Anda dapat menggunakan kode berikut:

> `$response` di bawah adalah `Symfony\Component\HttpFoundation\Response`, bukan `Hyperf\HttpMessage\Server\Response`.
> Jadi, cukup kembalikan konten `Body` secara langsung untuk melewati verifikasi WeChat.

```php
$response = $app->server->serve();

return $response->getContent();
```

## Cara Mengganti Cache

`EasyWeChat` menggunakan `file cache` secara default, tetapi dalam skenario dunia nyata, cache `Redis` lebih umum digunakan. Anda dapat menggantinya dengan komponen `hyperf/cache` yang disediakan oleh `Hyperf`. Jika Anda belum menginstal komponen ini, jalankan `composer require hyperf/cache`. Contohnya sebagai berikut:

```php
<?php
use Psr\SimpleCache\CacheInterface;
use Hyperf\Context\ApplicationContext;
use EasyWeChat\Factory;

$app = Factory::miniProgram([]);
$app['cache'] = ApplicationContext::getContainer()->get(CacheInterface::class);
```
