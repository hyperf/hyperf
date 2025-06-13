# Nacos SDK

## 安装

```shell
composer require hyperf/nacos
```

## 使用

```php
<?php

use Hyperf\Nacos\Application;
use Hyperf\Nacos\Config;
use Hyperf\Codec\Json;

$application = new Application(new Config([
    'username' => 'nacos',
    'password' => 'nacos',
    'guzzle_config' => [
        'headers' => [
            'charset' => 'UTF-8',
        ],
    ],
]));

$response = $application->auth->login('nacos', 'nacos');
$result = Json::decode((string) $response->getBody());

$response = $application->config->get('hyperf-service-config', 'DEFAULT_GROUP');
$result = Json::decode((string) $response->getBody());
```
