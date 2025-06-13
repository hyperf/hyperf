# ETCD

## 安裝

```
composer require hyperf/etcd
```

## 新增配置檔案 `etcd.php`

```php
<?php
return [
    'uri' => 'http://192.168.1.200:2379',
    'version' => 'v3beta',
    'options' => [
        'timeout' => 10,
    ],
];
```

## 使用

```php
<?php

use Hyperf\Context\ApplicationContext;
use Hyperf\Etcd\KVInterface;

$client = ApplicationContext::getContainer()->get(KVInterface::class);
```
