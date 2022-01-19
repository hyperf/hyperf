# Phar packager

## Install

```bash
composer require hyperf/phar
```

## use

- Default packaging

```shell
php bin/hyperf.php phar:build
```

- specify the package name

```shell
php bin/hyperf.php phar:build --name=your_project.phar
```

- specify package version

```shell
php bin/hyperf.php phar:build --phar-version=1.0.1
```

- Specify startup file

```shell
php bin/hyperf.php phar:build --bin=bin/hyperf.php
```

- Specify the package directory

```shell
php bin/hyperf.php phar:build --path=BASE_PATH
```

- Map external files

> requires hyperf/phar version >= v2.1.7

The following command allows the `phar` package to read the `.env` file in the same directory, so that `phar` can be distributed to various environments

```shell
php bin/hyperf.php phar:build -M .env
```

## run

```shell
php your_project.phar start
```

## Precautions

After packaging, it runs in the form of a `phar` package. Different from running in source code mode, the `runtime` directory in the `phar` package is not writable.
So we need to rewrite the partially writable directory location.

> Modify as appropriate according to the actual situation

- pid_file

Modify the `server.php` configuration.

```php
<?php

return [
    'settings' => [
        'pid_file' => '/tmp/runtime/hyperf.pid',
    ],
];
```

- logger

Modify `logger.php` configuration

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

The Phar packager will actively set `scan_cacheable` in the `config.php` configuration to `true`.

Of course, it is also possible to actively modify this configuration to `true`.
