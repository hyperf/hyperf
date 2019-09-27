# EasyWechat

[EasyWeChat](https://www.easywechat.com/) 是一个开源的 微信 非官方 SDK。

> 因为组件默认使用 `Curl`，所以我们需要修改对应的 `GuzzleClient` 为协程客户端，或者修改常量 `SWOOLE_HOOK_FLAGS` 为 `SWOOLE_HOOK_ALL | SWOOLE_HOOK_CURL`

## 替换 `Handler`

以下以小程序为例，

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

// 设置 HttpClient，当前设置没有实际效果，在数据请求时会被 guzzle_handler 覆盖，但不保证 EasyWeChat 后面会修改这里。
$config = $app['config']->get('http', []);
$config['handler'] = $container->get(HandlerStackFactory::class)->create();
$app->rebind('http_client', new Client($config));

// 重写 Handler
$app['guzzle_handler'] = new CoroutineHandler();

// 设置 OAuth 授权的 Guzzle 配置
AbstractProvider::setGuzzleOptions([
    'http_errors' => false,
    'handler' => HandlerStack::create(new CoroutineHandler()),
]);
```

## 修改 `SWOOLE_HOOK_FLAGS`

修改入口文件 `bin/hyperf.php`，以下忽略不需要修改的代码。

```php
<?php

! defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', SWOOLE_HOOK_ALL);

```