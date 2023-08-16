# EasyWechat

[EasyWeChat](https://www.easywechat.com/) 是一个开源的微信 SDK (非微信官方 SDK)。

> 如果您使用了 Swoole 4.7.0 及以上版本，并且开启了 native curl 选项，则可以不按照此文档进行操作。

> 因为组件默认使用 `Curl`，所以我们需要修改对应的 `GuzzleClient` 为协程客户端，或者修改常量 [SWOOLE_HOOK_FLAGS](/zh-cn/coroutine?id=swoole-runtime-hook-level)

## 替换 `Handler`

以下以公众号为例，

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

// 设置 HttpClient，部分接口直接使用了 http_client。
$config = $app['config']->get('http', []);
$config['handler'] = $stack = HandlerStack::create($handler);
$app->rebind('http_client', new Client($config));

// 部分接口在请求数据时，会根据 guzzle_handler 重置 Handler
$app['guzzle_handler'] = $handler;

// 如果使用的是 OfficialAccount，则还需要设置以下参数
$app->oauth->setGuzzleOptions([
    'http_errors' => false,
    'handler' => $stack,
]);
```

## 修改 `SWOOLE_HOOK_FLAGS`

参考 [SWOOLE_HOOK_FLAGS](/zh-cn/coroutine?id=swoole-runtime-hook-level)

## 如何使用 EasyWeChat

`EasyWeChat` 是为 `PHP-FPM` 架构设计的，所以在某些地方需要修改下才能在 Hyperf 下使用。下面我们以支付回调为例进行讲解。

1. `EasyWeChat` 中自带了 `XML` 解析，所以我们获取到原始 `XML` 即可。

```php
$xml = $this->request->getBody()->getContents();
```

2. 将 XML 数据放到 `EasyWeChat` 的 `Request` 中。

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

3. 服务器配置

如果需要使用微信公众平台的服务器配置功能，可以使用以下代码。

> 以下 `$response` 为 `Symfony\Component\HttpFoundation\Response` 并非 `Hyperf\HttpMessage\Server\Response` 
> 所以只需将 `Body` 内容直接返回，即可通过微信验证。

```php
$response = $app->server->serve();

return $response->getContent();
```

## 如何替换缓存

`EasyWeChat` 默认使用 `文件缓存`，而现实场景是 `Redis` 缓存居多，所以这里可以替换成 `Hyperf` 提供的 `hyperf/cache` 缓存组件，如您当前没有安装该组件，请执行 `composer require hyperf/cache` 引入，使用示例如下：

```php
<?php
use Psr\SimpleCache\CacheInterface;
use Hyperf\Context\ApplicationContext;
use EasyWeChat\Factory;

$app = Factory::miniProgram([]);
$app['cache'] = ApplicationContext::getContainer()->get(CacheInterface::class);
```
