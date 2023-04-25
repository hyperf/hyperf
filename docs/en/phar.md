# Phar packager

## Installation

```bash
composer require hyperf/phar
```

## Usage

- Packed by default

```shell
php bin/hyperf.php phar:build
```

- Set the package name

```shell
php bin/hyperf.php phar:build --name=your_project.phar
```

- Set the package version

```shell
php bin/hyperf.php phar:build --phar-version=1.0.1
```

- Set the startup file

```shell
php bin/hyperf.php phar:build --bin=bin/hyperf.php
```

- Set the packaging directory

```shell
php bin/hyperf.php phar:build --path=BASE_PATH
```

- Map external files

> Requires hyperf/phar version >= v2.1.7

The following command can allow the `phar` package to read the `.env` file in the same directory, so that `phar` can be distributed to various environments

```shell
php bin/hyperf.php phar:build -M .env
```

## run

```shell
php your_project.phar start
```

## Precautions

After packaging, it runs in the form of `phar` package, which is different from running in source code mode. The `runtime` directory in the `phar` package is not writable.
So we need to override some writable directory locations.

> Modify as appropriate according to the actual situation

- pid_file

Modify `server.php` configuration.

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

The Phar packager will automatically set `scan_cacheable` to `true` in `config.php` configuration.

Of course, it is also possible to actively modify this configuration to `true`.