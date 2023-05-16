#EasyWechat

[EasyWeChat](https://www.easywechat.com/) is an open source WeChat SDK (not WeChat official SDK).

> If you are using Swoole 4.7.0 and above, and have the native curl option turned on, you may not follow this document.

> Because the component uses `Curl` by default, we need to modify the corresponding `GuzzleClient` as a coroutine client, or modify the constant [SWOOLE_HOOK_FLAGS](/zh-cn/coroutine?id=swoole-runtime-hook-level)

## replace `Handler`

The following takes the public account as an example,

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

## Modify `SWOOLE_HOOK_FLAGS`

Reference [SWOOLE_HOOK_FLAGS](/en/coroutine?id=swoole-runtime-hook-level)

## How to use EasyWeChat

`EasyWeChat` is designed for `PHP-FPM` architecture, so it needs to be modified in some places to be used under Hyperf. Let's take the payment callback as an example to explain.

1. `EasyWeChat` comes with `XML` parsing, so we can get the original `XML`.

```php
$xml = $this->request->getBody()->getContents();
```

2. Put XML data into `Request` of `EasyWeChat`.

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

3. Server Configuration

If you need to use the server configuration function of the WeChat public platform, you can use the following code.

> The following `$response` is `Symfony\Component\HttpFoundation\Response` not `Hyperf\HttpMessage\Server\Response`
> So just return the `Body` content directly to pass WeChat verification.

```php
$response = $app->server->serve();

return $response->getContent();
```

## How to replace the cache

`EasyWeChat` uses `file cache` by default, but the actual scenario is that `Redis` cache is mostly used, so this can be replaced with the `hyperf/cache` cache component provided by `Hyperf`, if you do not currently install this component, please execute `composer Introduced by require hyperf/cache`, the usage example is as follows:

```php
<?php
use Psr\SimpleCache\CacheInterface;
use Hyperf\Context\ApplicationContext;
use EasyWeChat\Factory;

$app = Factory::miniProgram([]);
$app['cache'] = ApplicationContext::getContainer()->get(CacheInterface::class);
```
