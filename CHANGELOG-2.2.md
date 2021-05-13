# v2.2.0 - TBD

## Dependencies Upgrade

- Upgraded `friendsofphp/php-cs-fixer` to `^3.0`;
- Upgraded `psr/container` to `^1.0|^2.0`;
- Upgraded `egulias/email-validator` to `^3.0`;

## Dependencies Changed

- `domnikl/statsd` is abandoned and no longer maintained. The author suggests using the `slickdeals/statsd` package instead.

## Changed

- Changed the default priority of aspect to 0.
- `Hyperf\HttpServer\Server::__construct()` invoked with 5 parameters, `EventDispatcherInterface` is added.

## Added

- [#3579](https://github.com/hyperf/hyperf/pull/3579) Added event `RequestHandled`, which will be handled after each `request`.
