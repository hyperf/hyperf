# v3.2.0 - TBD

## Break Changes

1. `Carbon::createFromTimestamp()` don't read the default timezone by `date_default_timezone_get()` for `v3.0`.

```php
<?php

use Carbon\Carbon;

$t = time();

# The break usage
Carbon::createFromTimestamp($t, date_default_timezone_get());

# The correct usage
Carbon::createFromTimestamp($t, date_default_timezone_get());
```

2. The `logger` configuration structure has been changed. Please refer to [#7563](https://github.com/hyperf/hyperf/pull/7563).

```php
<?php

// Before
return [
    'default' => [
        'driver' => 'daily',
        'path' => BASE_PATH . '/runtime/logs/hyperf.log',
        'level' => env('LOG_LEVEL', 'debug'),
        'days' => 7,
    ],
];

// After
return [
    'default' => 'default',
    'channels' => [
        'default' => [
            'driver' => 'daily',
            'path' => BASE_PATH . '/runtime/logs/hyperf.log',
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 7,
        ],
        // Add your custom channels here
    ],
];
```

3. The `cache` configuration structure has been changed. Please refer to [#7594](https://github.com/hyperf/hyperf/pull/7594).

```php
<?php
// Before
return [
    'default' => [
        'driver' => RedisDriver::class,
        'packer' => PhpSerializerPacker::class,
        'prefix' => 'c:',
    ],
];

// After
return [
    'default' => env('CACHE_DRIVER', 'default'),
    'stores' => [
        'default' => [
            'driver' => RedisDriver::class,
            'packer' => PhpSerializerPacker::class,
            'prefix' => 'c:',
        ],
    ],
];
```

## Dependencies Upgrade

- Upgrade the php version to `>=8.2`
- Upgrade the `elasticsearch/elasticsearch` version to `>=8.0`
- Upgrade the `nikic/php-parser` version to `5.6`
- Upgrade the `symfony/*` components to `^6.0 || ^7.0`
- Upgrade the `phpunit/phpunit` version to `^11.0`

## Removed

- [#7278](https://github.com/hyperf/hyperf/pull/7278) Removed abandoned `laminas/laminas-mime` package.
- [#7573](https://github.com/hyperf/hyperf/pull/7573) Removed deprecated `Hyperf\Serializer\Contract\CacheableSupportsMethodInterface` interface.
- [#7609](https://github.com/hyperf/hyperf/pull/7609) Removed backward compatibility code from `Hyperf\AsyncQueue\JobMessage` serialization.
- [#7610](https://github.com/hyperf/hyperf/pull/7610) Removed deprecated code scheduled for v3.2 removal, including Collection backward compatibility, ProxyTrait parameter mapping, `ResumeExitCoordinatorListener`, SocketIO Future `flag` parameter, and WebSocket `HandeShakeException` typo alias.

## Optimized

- [#7142](https://github.com/hyperf/hyperf/pull/7142) Enhance array shuffle method to support custom random engines.

## Added

- [#6538](https://github.com/hyperf/hyperf/pull/6538) Support to specify the queue name based on the `job`.
- [#6591](https://github.com/hyperf/hyperf/pull/6591) Support `v3.0` for `nesbot/carbon`.
- [#6761](https://github.com/hyperf/hyperf/pull/6761) Added `toJson` method to `Hyperf\Contract\Jsonable`.
- [#6794](https://github.com/hyperf/hyperf/pull/6794) feat: Add Htmlable contract interface for HTTP responses.
- [#7019](https://github.com/hyperf/hyperf/pull/7019) Added PDO subclass support for PHP 8.4.
- [#7198](https://github.com/hyperf/hyperf/pull/7198) Added connection name to `QueryException`.
- [#7202](https://github.com/hyperf/hyperf/pull/7202) Added support for elasticsearch `8.x`.
- [#7214](https://github.com/hyperf/hyperf/pull/7214) Improve `Hyperf\Support\Fluent`.
- [#7247](https://github.com/hyperf/hyperf/pull/7247) Added `Hyperf\Pipeline\Pipeline::finally()`.
- [#7274](https://github.com/hyperf/hyperf/pull/7274) Support to take multiple items for `shift()` and `pop()` in `Hyperf\Collection\Collection`.
- [#7302](https://github.com/hyperf/hyperf/pull/7302) Added `partition()` and `reject()` to `Hyperf\Collection\Arr`.
- [#7312](https://github.com/hyperf/hyperf/pull/7312) Added `Macroable` support to `Hyperf\Context\Context`.
- [#7605](https://github.com/hyperf/hyperf/pull/7605) Added `NonCoroutine` attribute for flexible test execution control.
- [#7618](https://github.com/hyperf/hyperf/pull/7618) Added a new registration mode for async queue consumer processes that supports automatic registration based on configuration, eliminating the need for manual process registration in `config/autoload/processes.php`.

## Changed

- [#7208](https://github.com/hyperf/hyperf/pull/7208) Throw exceptions when the value is smaller than zero for `Hyperf\Database\Query\Builder::limit()`.
- [#6760](https://github.com/hyperf/hyperf/pull/6760) Changed the default type of `deleted_at` to `datetime` for `hyperf/database`.
- [#7563](https://github.com/hyperf/hyperf/pull/7563) Changed the `logger` configuration structure.
- [#7594](https://github.com/hyperf/hyperf/pull/7594) Changed the `cache` configuration structure.
- [#xxxx](https://github.com/hyperf/hyperf/pull/xxxx) Added Symfony 7.4 compatibility to `ApplicationFactory` by detecting and using `addCommand()` method when available, while maintaining backward compatibility with Symfony 6.x and 7.x.
