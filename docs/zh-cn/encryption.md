# 加密解密

[hyperf/encryption](https://github.com/hyperf/encryption) 借鉴于 `Laravel Encryption` 组件，十分感谢 `Laravel` 开发组对 `PHP` 社区的贡献。

## 简介

`Encryption` 是基于 `OpenSSL` 实现加密解密组件。

## 配置

加密器可以根据实际情况，配置多组 `key` 和 `cipher`。

```php
<?php

return [
    'default' => [
        'key' => 'Hyperf',
        'cipher' => 'AES-128-CBC',
    ],
];

```

## 使用

```php
<?php
use Hyperf\Utils\ApplicationContext;
use Hyperf\Encryption\Contract\EncrypterInterface;

$input = 'Hello Word.';
$container = ApplicationContext::getContainer();
$encrypter = $container->get(EncrypterInterface::class);
$encrypt = $encrypter->encrypt($input);
$raw = $encrypter->decrypt($encrypt);
```
