# EasyWeChat

[EasyWeChat](https://www.easywechat.com/) is an open-source WeChat SDK (not an official WeChat SDK).

> If you are using Swoole 4.7.0 or higher and have enabled the native curl option, you do not need to follow this documentation.

> Because the component uses `Curl` by default, we need to replace the corresponding `GuzzleClient` with a coroutine-friendly client, or modify the constant [SWOOLE_HOOK_FLAGS](../coroutine.md#swoole-runtime-hook-level).

## Replacing `Handler`

Taking the Official Account as an example:

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

// Set HttpClient; some interfaces use http_client directly.
$config = $app['config']->get('http', []);
$config['handler'] = $stack = HandlerStack::create($handler);
$app->rebind('http_client', new Client($config));

// Some interfaces reset the Handler based on guzzle_handler when requesting data.
$app['guzzle_handler'] = $handler;

// If you are using OfficialAccount, you also need to set the following parameters:
$app->oauth->setGuzzleOptions([
    'http_errors' => false,
    'handler' => $stack,
]);
```

## Modifying `SWOOLE_HOOK_FLAGS`

Refer to [SWOOLE_HOOK_FLAGS](../coroutine.md#swoole-runtime-hook-level).

## How to use EasyWeChat

`EasyWeChat` is designed for the `PHP-FPM` architecture, so it requires some modifications to work properly in Hyperf. Below, we will use payment callbacks as an example to explain.

1. `EasyWeChat` comes with `XML` parsing, so we just need to get the raw `XML`.

```php
$xml = $this->request->getBody()->getContents();
```

2. Put the XML data into the `Request` object of `EasyWeChat`.

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

If you need to use the server configuration function of the WeChat Official Account platform, you can use the following code:

> The `$response` below is `Symfony\Component\HttpFoundation\Response`, not `Hyperf\HttpMessage\Server\Response`.
> Therefore, simply return the `Body` content directly to pass the WeChat verification.

```php
$response = $app->server->serve();

return $response->getContent();
```

## How to replace the cache

`EasyWeChat` uses `file cache` by default, but in real-world scenarios, `Redis` cache is more commonly used. You can replace it with the `hyperf/cache` component provided by `Hyperf`. If you haven't installed this component, please execute `composer require hyperf/cache` to introduce it. An example is as follows:

```php
<?php
use Psr\SimpleCache\CacheInterface;
use Hyperf\Context\ApplicationContext;
use EasyWeChat\Factory;

$app = Factory::miniProgram([]);
$app['cache'] = ApplicationContext::getContainer()->get(CacheInterface::class);
```
