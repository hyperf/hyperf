# Phar 打包器

## 安裝

```bash
composer require hyperf/phar
```

## 使用

- 默認打包

```shell
php bin/hyperf.php phar:build
```

- 指定包名

```shell
php bin/hyperf.php phar:build --name=your_project.phar
```

- 指定包版本

```shell
php bin/hyperf.php phar:build --phar-version=1.0.1
```

- 指定啟動文件

```shell
php bin/hyperf.php phar:build --bin=bin/hyperf.php
```

- 指定打包目錄

```shell
php bin/hyperf.php phar:build --path=BASE_PATH
```

- 映射外部文件

> 需要 hyperf/phar 版本 >= v2.1.7

下述命令，可以允許 `phar` 包讀取同目錄的 `.env` 文件，方便 `phar` 分發到各個環境當中

```shell
php bin/hyperf.php phar:build -M .env
```

## 運行

```shell
php your_project.phar start
```

## 注意事項

打包後是以 `phar` 包的形式運行，不同與源代碼模式運行，`phar` 包中的 `runtime` 目錄是不可寫的，
所以我們需要重寫部分可寫的目錄位置。

> 根據實際情況酌情修改

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

Phar 打包器會將 `config.php` 配置中的 `scan_cacheable` 主動設置為 `true`。

當然，主動修改此配置為 `true`，也是可以的。
