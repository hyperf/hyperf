# EasyWeChat

[EasyWeChat](https://www.easywechat.com/) adalah SDK WeChat open source
(bukan SDK resmi WeChat).

> Jika Anda menggunakan Swoole 4.7.0 ke atas, dan telah mengaktifkan opsi
> native curl, Anda tidak perlu mengikuti dokumen ini.

> Karena komponen ini menggunakan `Curl` secara default, kita perlu mengubah
> `GuzzleClient` terkait menjadi coroutine client, atau mengubah konstanta
> [SWOOLE_HOOK_FLAGS](/id/coroutine?id=swoole-runtime-hook-level)

## Mengganti `Handler`

Berikut ini adalah contoh menggunakan official account,

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

// Set HttpClient, some interfaces use http_client directly.
$config = $app['config']->get('http', []);
$config['handler'] = $stack = HandlerStack::create($handler);
$app->rebind('http_client', new Client($config));

// Some interfaces will reset the Handler according to guzzle_handler when requesting data
$app['guzzle_handler'] = $handler;

// If you are using OfficialAccount, you also need to set the following parameters
$app->oauth->setGuzzleOptions([
    'http_errors' => false,
    'handler' => $stack,
]);
```

## Mengubah `SWOOLE_HOOK_FLAGS`

Referensi [SWOOLE_HOOK_FLAGS](/id/coroutine?id=swoole-runtime-hook-level)

## Cara Menggunakan EasyWeChat

`EasyWeChat` dirancang untuk arsitektur `PHP-FPM`, sehingga perlu disesuaikan
di beberapa bagian agar dapat digunakan di bawah Hyperf. Mari kita ambil contoh
callback pembayaran untuk menjelaskannya.

1. `EasyWeChat` dilengkapi dengan parsing `XML` bawaan, sehingga kita bisa
mendapatkan `XML` asli.

```php
$xml = $this->request->getBody()->getContents();
```

2. Masukkan data XML ke dalam `Request` `EasyWeChat`.

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
// Do something...

```

3. Konfigurasi Server

Jika Anda perlu menggunakan fungsi konfigurasi server dari platform publik
WeChat, Anda dapat menggunakan kode berikut.

> `$response` berikut ini adalah `Symfony\Component\HttpFoundation\Response`
> dan bukan `Hyperf\HttpMessage\Server\Response`. Jadi, cukup kembalikan
> konten `Body` secara langsung untuk lolos verifikasi WeChat.

```php
$response = $app->server->serve();

return $response->getContent();
```

## Cara Mengganti Cache

`EasyWeChat` menggunakan `file cache` secara default, tetapi dalam skenario
nyata, cache `Redis` lebih sering digunakan. Oleh karena itu, ini dapat diganti
dengan komponen cache `hyperf/cache` yang disediakan oleh `Hyperf`. Jika Anda
belum menginstal komponen ini, silakan jalankan `composer require hyperf/cache`.
Contoh penggunaannya adalah sebagai berikut:

```php
<?php
use Psr\SimpleCache\CacheInterface;
use Hyperf\Context\ApplicationContext;
use EasyWeChat\Factory;

$app = Factory::miniProgram([]);
$app['cache'] = ApplicationContext::getContainer()->get(CacheInterface::class);
```
