# v2.1.0 - TBD
    
## Dependencies Upgrade

- Upgraded `phpunit/phpunit` to `^9.0`;
- Upgraded `guzzlehttp/guzzle` to `^6.0|^7.0`;
- Upgraded `vlucas/phpdotenv` to `^5.0`;
- Upgraded `endclothing/prometheus_client_php` to `^1.0`;
- Upgraded `twig/twig` to `^3.0`;

## Removed

- Removed deprecated property `$name` from `Hyperf\Amqp\Builder`.
- Removed deprecated method `consume` from `Hyperf\Amqp\Message\ConsumerMessageInterface`.
- Removed deprecated property `$running` from `Hyperf\AsyncQueue\Driver\Driver`.
- Removed deprecated method `parseParameters` from `Hyperf\HttpServer\CoreMiddleware`.
- Removed deprecated const `ON_WORKER_START` and `ON_WORKER_EXIT` from `Hyperf\Utils\Coordinator\Constants`.
- Removed deprecated method `get` from `Hyperf\Utils\Coordinator`.
- Removed config `rate-limit.php`, please use `rate_limit.php` instead.
- Removed useless class `Hyperf\Resource\Response\ResponseEmitter`.

## Deprecated

- `Hyperf\AsyncQueue\Signal\DriverStopHandler` will be deprecated in v2.2, please use `Hyperf\Process\Handler\ProcessStopHandler` instead.

## Added

- [#2659](https://github.com/hyperf/hyperf/pull/2659) Support `HttpServer` for [Swow](https://github.com/swow/swow).
