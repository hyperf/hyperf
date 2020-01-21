# EasyWechat

[EasyWeChat](https://www.easywechat.com/) 是一個開源的微信 SDK (非微信官方 SDK)。

> 因為元件預設使用 `Curl`，所以我們需要修改對應的 `GuzzleClient` 為協程客戶端，或者修改常量 `SWOOLE_HOOK_FLAGS` 為 `SWOOLE_HOOK_ALL | SWOOLE_HOOK_CURL`

## 替換 `Handler`

以下以小程式為例，

```php
<?php

use Hyperf\Utils\ApplicationContext;
use EasyWeChat\Factory;
use EasyWeChat\Kernel\ServiceContainer;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Hyperf\Guzzle\CoroutineHandler;
use Hyperf\Guzzle\HandlerStackFactory;
use Overtrue\Socialite\Providers\AbstractProvider;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RedirectResponse;

$container = ApplicationContext::getContainer();

$app = Factory::miniProgram($config);

// 設定 HttpClient，當前設定沒有實際效果，在資料請求時會被 guzzle_handler 覆蓋，但不保證 EasyWeChat 後面會修改這裡。
$config = $app['config']->get('http', []);
$config['handler'] = $container->get(HandlerStackFactory::class)->create();
$app->rebind('http_client', new Client($config));

// 重寫 Handler
$app['guzzle_handler'] = new CoroutineHandler();

// 設定 OAuth 授權的 Guzzle 配置
AbstractProvider::setGuzzleOptions([
    'http_errors' => false,
    'handler' => HandlerStack::create(new CoroutineHandler()),
]);
```

## 修改 `SWOOLE_HOOK_FLAGS`

修改入口檔案 `bin/hyperf.php`，以下忽略不需要修改的程式碼。

```php
<?php

! defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', SWOOLE_HOOK_ALL | SWOOLE_HOOK_CURL);

```

## 如何使用 EasyWeChat

`EasyWeChat` 是為 `PHP-FPM` 架構設計的，所以在某些地方需要修改下才能在 Hyperf 下使用。下面我們以支付回撥為例進行講解。

1. `EasyWeChat` 中自帶了 `XML` 解析，所以我們獲取到原始 `XML` 即可。

```php
$xml = $this->request->getBody()->getContents();
```

2. 將 XML 資料放到 `EasyWeChat` 的 `Request` 中。

```php
<?php
use Symfony\Component\HttpFoundation\Request;

$get = $this->request->getQueryParams();
$post = $this->request->getParsedBody();
$cookie = $this->request->getCookieParams();
$files = $this->request->getUploadedFiles();
$server = $this->request->getServerParams();
$xml = $this->request->getBody()->getContents();

$app['request'] = new Request($get,$post,[],$cookie,$files,$server,$xml);

// Do something...
```

## 如何替換快取

`EasyWeChat` 預設使用 `檔案快取`，而現實場景是 `Redis` 快取居多，所以這裡可以替換成 `Hyperf` 提供的 `hyperf/cache` 快取元件，如您當前沒有安裝該元件，請執行 `composer require hyperf/cache` 引入，使用示例如下：

```php
<?php
use Psr\SimpleCache\CacheInterface;
use Hyperf\Utils\ApplicationContext;
use EasyWeChat\Factory;

$app = Factory::miniProgram([]);
$app['cache'] = ApplicationContext::getContainer()->get(CacheInterface::class);

```
