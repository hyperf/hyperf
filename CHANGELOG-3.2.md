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

## Dependencies Upgrade

- Upgrade the php version to `>=8.2`
- Upgrade the `elasticsearch/elasticsearch` version to `>=8.0`
- Upgrade the `nikic/php-parser` version to `5.6`
- Upgrade the `symfony/*` components to `^6.0 || ^7.0`

## Removed

- [#7278](https://github.com/hyperf/hyperf/pull/7278) Removed abandoned `laminas/laminas-mime` package.
- [#7573](https://github.com/hyperf/hyperf/pull/7573) Removed deprecated `Hyperf\Serializer\Contract\CacheableSupportsMethodInterface` interface.

## Added

- [#6538](https://github.com/hyperf/hyperf/pull/6538) Support to specify the queue name based on the `job`.
- [#6591](https://github.com/hyperf/hyperf/pull/6591) Support `v3.0` for `nesbot/carbon`.
- [#6761](https://github.com/hyperf/hyperf/pull/6761) Added `toJson` method to `Hyperf\Contract\Jsonable`.
- [#7198](https://github.com/hyperf/hyperf/pull/7198) Added connection name to `QueryException`.
- [#7202](https://github.com/hyperf/hyperf/pull/7202) Added support for elasticsearch `8.x`.
- [#7214](https://github.com/hyperf/hyperf/pull/7214) Improve `Hyperf\Support\Fluent`.
- [#7247](https://github.com/hyperf/hyperf/pull/7247) Added `Hyperf\Pipeline\Pipeline::finally()`.
- [#7274](https://github.com/hyperf/hyperf/pull/7274) Support to take multiple items for `shift()` and `pop()` in `Hyperf\Collection\Collection`.
- [#7302](https://github.com/hyperf/hyperf/pull/7302) Added `partition()` and `reject()` to `Hyperf\Collection\Arr`.
- [#7312](https://github.com/hyperf/hyperf/pull/7312) Added `Macroable` support to `Hyperf\Context\Context`.

## Changed

- [#7208](https://github.com/hyperf/hyperf/pull/7208) Throw exceptions when the value is smaller than zero for `Hyperf\Database\Query\Builder::limit()`.
