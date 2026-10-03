# ETCD

## Instalasi

```
composer require hyperf/etcd
```

## Tambahkan file konfigurasi `etcd.php`

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

## Penggunaan

```php
<?php

use Hyperf\Context\ApplicationContext;
use Hyperf\Etcd\KVInterface;

$client = ApplicationContext::getContainer()->get(KVInterface::class);
```
