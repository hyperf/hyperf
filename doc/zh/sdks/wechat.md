# EasyWechat

EasyWeChat 是一个开源的 微信 非官方 SDK。

> 因为组件默认使用 `Curl`，所以我们需要修改对应的 `GuzzleClient` 为协程客户端，或者修改常量 `SWOOLE_HOOK_FLAGS` 为 `SWOOLE_HOOK_ALL | SWOOLE_HOOK_CURL`

## 替换 `Handler`

以下以小程序为例，

```php
<?php

$app = Factory::miniProgram($config);
$app['guzzle_handler'] = CoroutineHandler::class;
```

## 修改 `SWOOLE_HOOK_FLAGS`

修改入口文件 `bin/hyperf.php`，以下忽略不需要修改的代码。

```php
<?php

! defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', SWOOLE_HOOK_ALL);

```