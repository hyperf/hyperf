# Added Coroutine Server

- Changed method `bind(Server $server)` to `bind($server)` in `Hyperf\Contract\ProcessInterface`.
- Changed method `isEnable()` to `isEnable($server)` in `Hyperf\Contract\ProcessInterface`
- Process of config-center, crontab, metric must not run in co-server.
- `Hyperf\AsyncQueue\Environment` only applies to the current coroutine, not process.
