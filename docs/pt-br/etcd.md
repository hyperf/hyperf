# ETCD

## Instalação

```
composer require hyperf/etcd
```

## Adicionar o arquivo de configuração `etcd.php`

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

## Uso

```php
<?php

use Hyperf\Context\ApplicationContext;
use Hyperf\Etcd\KVInterface;

$client = ApplicationContext::getContainer()->get(KVInterface::class);
```
