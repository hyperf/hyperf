# v3.2.0 - TBD

## Dependencies Upgrade

- Upgrade the php version to `>=8.2`
- Upgrade the `elasticsearch/elasticsearch` version to `>=8.0`

## Removed

- [#7278](https://github.com/hyperf/hyperf/pull/7278) Removed abandoned `laminas/laminas-mime` package.

## Added

- [#6538](https://github.com/hyperf/hyperf/pull/6538) Support to specify the queue name based on the `job`.
- [#6761](https://github.com/hyperf/hyperf/pull/6761) Added `toJson` method to `Hyperf\Contract\Jsonable`.
- [#7198](https://github.com/hyperf/hyperf/pull/7198) Added connection name to `QueryException`.
- [#7202](https://github.com/hyperf/hyperf/pull/7202) Added support for elasticsearch `8.x`.
- [#7214](https://github.com/hyperf/hyperf/pull/7214) Improve `Hyperf\Support\Fluent`.
- [#7247](https://github.com/hyperf/hyperf/pull/7247) Added `Hyperf\Pipeline\Pipeline::finally()`.

## Changed

- [#7208](https://github.com/hyperf/hyperf/pull/7208) Throw exceptions when the value is smaller than zero for `Hyperf\Database\Query\Builder::limit()`.
