# Phar 打包器

## 安装

```bash
composer require hyperf/phar
```

## 使用

- 默认打包

```shell
php bin/hyperf.php phar:build
```

- 指定包名

```shell
php bin/hyperf.php phar:build --name=your_project.phar
```

- 指定启动文件

```shell
php bin/hyperf.php phar:build --bin=bin/hyperf.php
```

- 指定打包目录

```shell
php bin/hyperf.php phar:build --path=BASE_PATH
```

## 运行

```shell
php your_project.phar start
```

## 注意事项

打包后是以 `phar` 包的形式运行，不同与源代码模式运行，`phar` 包中的 `runtime` 目录是不可写的，
所以我们需要重写部分可写的目录位置。

> 根据实际情况酌情修改

- pid_file

修改 `server.php` 配置。

```php
<?php

return [
    'settings' => [
        'pid_file' => '/tmp/runtime/hyperf.pid',
    ],
];
```

- logger

修改 `logger.php` 配置

```php
<?php
return [
    'default' => [
        'handler' => [
            'class' => Monolog\Handler\StreamHandler::class,
            'constructor' => [
                'stream' => '/tmp/runtime/logs/hyperf.log',
                'level' => Monolog\Logger::INFO,
            ],
        ],
    ],
];
```

- scan_cacheable

Phar 打包器会将 `config.php` 配置中的 `scan_cacheable` 主动设置为 `true`。

暂时因为 `Ast` 不支持复杂的代码重写，所以建议使用默认格式，不然可能导致项目无法启动。

当然，主动修改此配置为 `true`，也是可以的。
