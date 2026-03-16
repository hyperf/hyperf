# EasyWechat

[EasyWeChat](https://www.easywechat.com/) 是一個開源的微信 SDK (非微信官方 SDK)。

> 如果您使用了 Swoole 4.7.0 及以上版本，並且開啓了 native curl 選項，則可以不按照此文檔進行操作。

> 因為組件默認使用 `Curl`，所以我們需要修改對應的 `GuzzleClient` 為協程客户端，或者修改常量 [SWOOLE_HOOK_FLAGS](/zh-hk/coroutine?id=swoole-runtime-hook-level)

## 替換 `Handler`

以下以公眾號為例，

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

// 設置 HttpClient，部分接口直接使用了 http_client。
$config = $app['config']->get('http', []);
$config['handler'] = $stack = HandlerStack::create($handler);
$app->rebind('http_client', new Client($config));

// 部分接口在請求數據時，會根據 guzzle_handler 重置 Handler
$app['guzzle_handler'] = $handler;

// 如果使用的是 OfficialAccount，則還需要設置以下參數
$app->oauth->setGuzzleOptions([
    'http_errors' => false,
    'handler' => $stack,
]);
```

## 修改 `SWOOLE_HOOK_FLAGS`

參考 [SWOOLE_HOOK_FLAGS](/zh-hk/coroutine?id=swoole-runtime-hook-level)

## 如何使用 EasyWeChat

`EasyWeChat` 是為 `PHP-FPM` 架構設計的，所以在某些地方需要修改下才能在 Hyperf 下使用。下面我們以支付回調為例進行講解。

1. `EasyWeChat` 中自帶了 `XML` 解析，所以我們獲取到原始 `XML` 即可。

```php
$xml = $this->request->getBody()->getContents();
```

2. 將 XML 數據放到 `EasyWeChat` 的 `Request` 中。

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

3. 服務器配置

如果需要使用微信公眾平台的服務器配置功能，可以使用以下代碼。

> 以下 `$response` 為 `Symfony\Component\HttpFoundation\Response` 並非 `Hyperf\HttpMessage\Server\Response` 
> 所以只需將 `Body` 內容直接返回，即可通過微信驗證。

```php
$response = $app->server->serve();

return $response->getContent();
```

## 如何替換緩存

`EasyWeChat` 默認使用 `文件緩存`，而現實場景是 `Redis` 緩存居多，所以這裏可以替換成 `Hyperf` 提供的 `hyperf/cache` 緩存組件，如您當前沒有安裝該組件，請執行 `composer require hyperf/cache` 引入，使用示例如下：

```php
<?php
use Psr\SimpleCache\CacheInterface;
use Hyperf\Context\ApplicationContext;
use EasyWeChat\Factory;

$app = Factory::miniProgram([]);
$app['cache'] = ApplicationContext::getContainer()->get(CacheInterface::class);
```
