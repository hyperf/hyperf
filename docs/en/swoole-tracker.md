# Swoole Tracker

[Swoole Tracker](https://business.swoole.com/tracker/index) is a set of enterprise-level tools officially powered by Swoole, including PHP and Swoole analysis, debugging tools, and application performance management (APM) platform. The Swoole Tracker focus on conventional FPM and Swoole resident process business, provide comprehensive performance monitoring, analysis and debugging solutions. (former name: Swoole Enterprise)

Swoole Tracker can help companies automatically analyze and summarize important system calls and locate specific PHP business codes, intelligently and accurately. It optimize business application performance, and provides a powerful debugging tool chain to escort corporate business and improve IT production efficiency.

- Keep abreast of the application architecture model
> Automatically discover the topological structure of applications and display it. Keep abreast of application architecture models.

- Distributed cross-application link tracking
> Support non-intrusive distributed cross-application link tracking, making each request clear at a glance. Fully supporting coroutine/non-coroutine environment. Support real-time data visualization.

- Comprehensive analysis and reporting of service status
> Invocation information reported by the service in various dimensions, such as total flow, average time consumption, timeout rate, etc., and comprehensively analyze and report the service status.

- Powerful debugging toolchain
> The system supports remote debugging, which can be remotely opened in the background of the system to perform memory leaks detection, block detection, code performance analysis, and check the call stack. It also supports manual debugging and viewing results in the background.

- Support FPM and Swoole
> Perfect support for PHP-FPM environment, not limited to use in Swoole.

- Complete system monitoring
> Support complete system monitoring, zero-cost deployment, monitor the CPU, memory, network, disk and other resources, which can be easily integrated into the existing alarm system.

- Install with one simple click and zero-cost access
> Avoid and reduce the overall investment risk. The client of this system provides scripts that can be deployed with one click, and the server can run in the Docker environment, which is convenient.

- Improve production efficiency of various departments
> Track service and code-level performance bottlenecks in complex systems, help development departments improve work efficiency, and focus on core business work.

## Installation

### Install extension

After registering an account, enter the [console](https://business.swoole.com/SwooleTracker/catdemo) to apply for a trial and download the corresponding installation script.

Relevant docs [Basic](https://www.kancloud.cn/swoole-inc/ee-base-wiki/1214079) and [Help](https://www.kancloud.cn/swoole-inc/ee-help-wiki/1213080)

Copy the script and the following two files to the `.build` in the project directory.

1. `entrypoint.sh`

```bash
#!/usr/bin/env sh

/opt/swoole/script/php/swoole_php /opt/swoole/node-agent/src/node.php &

php /opt/www/bin/hyperf.php start

```

2. `swoole_tracker.ini`

```ini
[swoole_tracker]
extension=/opt/.build/swoole_tracker.so
;enable apm
apm.enable=1
;sampling rate, for example: 100%
apm.sampling_rate=100
;memory leak detection, default 0 (off)
apm.enable_memcheck=1

;Tracker has been modified as a Zend extension since v3.3.0
zend_extension=swoole_tracker.so
tracker.enable=1
tracker.sampling_rate=100
tracker.enable_memcheck=1
```

Then copy the following `Dockerfile` to the project root directory.

```dockerfile
# Default Dockerfile
#
# @link     https://www.hyperf.io
# @document https://hyperf.wiki
# @contact  group@hyperf.io
# @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE

FROM hyperf/hyperf:7.4-alpine-v3.11-swoole
LABEL maintainer="Hyperf Developers <group@hyperf.io>" version="1.0" license="MIT" app.name="Hyperf"

##
# ---------- env settings ----------
##
# --build-arg timezone=Asia/Shanghai
ARG timezone

ENV TIMEZONE=${timezone:-"Asia/Shanghai"} \
    APP_ENV=prod \
    SCAN_CACHEABLE=(true)

# update
RUN set -ex \
    # show php version and extensions
    && php -v \
    && php -m \
    && php --ri swoole \
    #  ---------- some config ----------
    && cd /etc/php7 \
    # - config PHP
    && { \
        echo "upload_max_filesize=128M"; \
        echo "post_max_size=128M"; \
        echo "memory_limit=1G"; \
        echo "date.timezone=${TIMEZONE}"; \
    } | tee conf.d/99_overrides.ini \
    # - config timezone
    && ln -sf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime \
    && echo "${TIMEZONE}" > /etc/timezone \
    # ---------- clear works ----------
    && rm -rf /var/cache/apk/* /tmp/* /usr/share/man \
    && echo -e "\033[42;37m Build Completed :).\033[0m\n"

COPY .build /opt/.build
WORKDIR /opt/.build

RUN chmod +x swoole-tracker-install.sh \
    && ./swoole-tracker-install.sh \
    && chmod 755 entrypoint.sh \
    && cp swoole-tracker/swoole_tracker74.so /opt/.build/swoole_tracker.so \
    && cp swoole_tracker.ini /etc/php7/conf.d/98_swoole_tracker.ini \
    && php -m

WORKDIR /opt/www

# Composer Cache
# COPY ./composer.* /opt/www/
# RUN composer install --no-dev --no-scripts

COPY . /opt/www
RUN composer install --no-dev -o && php bin/hyperf.php

EXPOSE 9501

ENTRYPOINT ["sh", "/opt/.build/entrypoint.sh"]

```

## Usage

### Component-free way

The `v2.5.0` and above versions of `Swoole Tracker` support automatically generating application names and creating applications without modifying any code.

If you use the `HttpServer` of `Swoole`, then the generated application name is `ip:port`

If you use other `Server` of `Swoole`, then the generated application name is `ip(hostname):port`

After installing the `swoole_tracker` extension, you can then use the `Swoole Tracker` normally

### With Component way

When you need to customize the application name, you need to install the component. 
Use `Composer` to install:

```bash
composer require hyperf/swoole-tracker
```

After the installation, register the `Hyperf\SwooleTracker\Middleware\HttpServerMiddleware` middleware in the `config/autoload/middlewares.php` configuration file, as follows:

```php
<?php

return [
    'http' => [
        Hyperf\SwooleTracker\Middleware\HttpServerMiddleware::class
    ],
];
```

If you use the `jsonrpc-http` protocol to implement the `RPC` service, you also need to configure the following `Aspect` in `config/autoload/aspects.php`:

```php
<?php

return [
    Hyperf\SwooleTracker\Aspect\CoroutineHandlerAspect::class,
];
```

## Free memory leak detection tool

Swoole Tracker is a commercial product that has the ability to detect memory leaks. However, Swoole Tracker provides the function of memory leak detection to the PHP community for free to improve the PHP ecosystem and show thanks and respect to the community. The following will outline its usage.

1. Go to [Swoole Tracker Web](https://business.swoole.com/SwooleTracker/download/) to download the latest Swoole Tracker extension;

2. Same as what mentioned above to add the extension, and add another line of configuration:

```ini
;enable leak detection
apm.enable_malloc_hook=1
```

!> Note: Do not enable it when composer installs dependencies. Do not enable it when generating proxy class cache.

1. According to your own business, add a call of `trackerHookMalloc()` at the beginning of Swoole `onReceive` or `onRequest` event:

```php
$http->on('request', function ($request, $response) {
    trackerHookMalloc();
    $response->end("<h1>Hello Swoole. #".rand(1000, 9999)."</h1>");
});
```

After each call ends (the first call will not be recorded), a leaked message will be generated in the `/tmp/trackerleak` log. We can use the `trackerAnalyzeLeak()` function on the CLI command line to analyze the leak log to generate a report.

```shell
php -r "trackerAnalyzeLeak();"
```

Report form will be like the following:

when there is no leak:

```
[16916 (Loop 5)] ✅ Nice!! No Leak Were Detected In This Loop
```

where `16916` represents process id, and `Loop 5` means the leak message in the 5th time main function calling.

when there are certain leak:

```
[24265 (Loop 8)] /tests/mem_leak/http_server.php:125 => [12928]
[24265 (Loop 8)] /tests/mem_leak/http_server.php:129 => [12928]
[24265 (Loop 8)] ❌ This Loop TotalLeak: [25216]
```

It means that lines 125 and 129 of `http_server.php` respectively leaked 12928 bytes of memory (in total 25216 bytes) in the 8th time calling.

You can clear the leak log by calling `trackerCleanLeak()`. [For more details about the memory detection tools](https://www.kancloud.cn/swoole-inc/ee-help-wiki/1941569)

If you need to detect memory leaks in HTTP Server in Hyperf, you can add a global middleware in `config/autoload/middlewares.php`:

```php
<?php

return [
    'http' => [
        Hyperf\SwooleTracker\Middleware\HookMallocMiddleware::class,
    ],
];
```

The same to apply to other Server types.
