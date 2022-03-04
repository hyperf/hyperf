# Swoole Tracker

[Swoole Tracker](https://business.swoole.com/tracker/index)是 Swoole 官方推出的一整套企业级包括 PHP 和  Swoole 分析调试工具以及应用性能管理（APM）平台，针对常规的 FPM 和 Swoole 常驻进程的业务，提供全面的性能监控、分析和调试的解决方案。（曾命名：Swoole Enterprise）

Swoole Tracker 能够帮助企业自动分析并汇总统计关键系统调用并智能准确的定位到具体的 PHP 业务代码，实现业务应用性能最优化、强大的调试工具链为企业业务保驾护航、提高 IT 生产效率。

- 时刻掌握应用架构模型
> 自动发现应用依赖拓扑结构和展示，时刻掌握应用的架构模型

- 分布式跨应用链路追踪
> 支持无侵入的分布式跨应用链路追踪，让每个请求一目了然，全面支持协程/非协程环境，数据实时可视化

- 全面分析报告服务状况
> 各种维度统计服务上报的调用信息， 比如总流量、平均耗时、超时率等，并全面分析报告服务状况

- 拥有强大的调试工具链
> 本系统支持远程调试，可在系统后台远程开启检测内存泄漏、阻塞检测、代码性能分析和查看调用栈；也支持手动埋点进行调试，后台统一查看结果

- 同时支持 FPM 和 Swoole
> 完美支持 PHP-FPM 环境，不仅限于在 Swoole 中使用

- 完善的系统监控
> 支持完善的系统监控，零成本部署，监控机器的 CPU、内存、网络、磁盘等资源，可以很方便的集成到现有报警系统

- 一键安装和零成本接入
> 规避与减小整体投资风险，本系统的客户端提供脚本可一键部署，服务端可在 Docker 环境中运行，简单快捷

- 提高各部门生产效率
> 在复杂系统中追踪服务及代码层级性能瓶颈，帮助 IT、开发等部门提升工作效率，将重点聚焦在核心工作中

## 安装

### 安装扩展

注册完账户后，进入[控制台](https://business.swoole.com/SwooleTracker/catdemo)，并申请试用，下载对应的安装脚本。

相关文档，请移步 [试用文档](https://www.kancloud.cn/swoole-inc/ee-base-wiki/1214079) 或 [详细文档](https://www.kancloud.cn/swoole-inc/ee-help-wiki/1213080) 

将脚本以及以下两个文件复制到项目目录 `.build` 中

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
;打开总开关
apm.enable=1
;采样率 例如：100%
apm.sampling_rate=100
;开启内存泄漏检测时添加 默认0 关闭状态
apm.enable_memcheck=1

;Tracker从v3.3.0版本开始修改为了Zend扩展
zend_extension=swoole_tracker.so
tracker.enable=1
tracker.sampling_rate=100
tracker.enable_memcheck=1
```

然后将下面的 `Dockerfile` 复制到项目根目录中。

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

## 使用

### 不依赖组件

`Swoole Tracker` 的 `v2.5.0` 以上版本支持自动生成应用名称并创建应用，无需修改任何代码。

如果使用 `Swoole` 的 `HttpServer` 那么生成的应用名称为`ip:port`

如果使用 `Swoole` 其他的 `Server` 那么生成的应用名称为`ip(hostname):port`

即安装好 `swoole_tracker` 扩展之后就可以正常使用 `Swoole Tracker` 的功能

### 依赖组件

当你需要自定义应用名称时则需要安装组件，使用 `Composer` 安装：

```bash
composer require hyperf/swoole-tracker
```

安装完成后在 `config/autoload/middlewares.php` 配置文件中注册 `Hyperf\SwooleTracker\Middleware\HttpServerMiddleware` 中间件即可，如下：

```php
<?php

return [
    'http' => [
        Hyperf\SwooleTracker\Middleware\HttpServerMiddleware::class
    ],
];
```

若使用 `jsonrpc-http` 协议实现了 `RPC` 服务，则还需要在 `config/autoload/aspects.php` 配置以下 `Aspect`：

```php
<?php

return [
    Hyperf\SwooleTracker\Aspect\CoroutineHandlerAspect::class,
];
```

## 免费内存泄漏检测工具

Swoole Tracker 本是一款商业产品，拥有进行内存泄漏检测的能力，不过 Swoole Tracker 把内存泄漏检测的功能完全免费给 PHP 社区使用，完善 PHP 生态，回馈社区，下面将概述它的具体用法。

1. 前往 [Swoole Tracker 官网](https://business.swoole.com/SwooleTracker/download/) 下载最新的 Swoole Tracker 扩展；

2. 和上文添加扩展相同，再加入一行配置：

```ini
;Leak检测开关
apm.enable_malloc_hook=1
```

!> 注意：不要在 composer 安装依赖时开启；不要在生成代理类缓存时开启。

3. 根据自己的业务，在 Swoole 的 onReceive 或者 onRequest 事件开头加上 `trackerHookMalloc()` 调用：

```php
$http->on('request', function ($request, $response) {
    trackerHookMalloc();
    $response->end("<h1>Hello Swoole. #".rand(1000, 9999)."</h1>");
});
```

每次调用结束后（第一次调用不会被记录），都会生成一个泄漏的信息到 `/tmp/trackerleak` 日志中，我们可以在 Cli 命令行调用 `trackerAnalyzeLeak()` 函数即可分析泄漏日志，生成泄漏报告

```shell
php -r "trackerAnalyzeLeak();"
```

下面是泄漏报告的格式：

没有内存泄漏的情况：

```
[16916 (Loop 5)] ✅ Nice!! No Leak Were Detected In This Loop
```

其中 `16916` 表示进程 id，`Loop 5`表示第 5 次调用主函数生成的泄漏信息

有确定的内存泄漏：

```
[24265 (Loop 8)] /tests/mem_leak/http_server.php:125 => [12928]
[24265 (Loop 8)] /tests/mem_leak/http_server.php:129 => [12928]
[24265 (Loop 8)] ❌ This Loop TotalLeak: [25216]
```

表示第 8 次调用 `http_server.php` 的 125 行和 129 行，分别泄漏了 12928 字节内存，总共泄漏了 25216 字节内存。

通过调用 `trackerCleanLeak()` 可以清除泄漏日志，重新开始。[了解更多内存检测工具使用细节](https://www.kancloud.cn/swoole-inc/ee-help-wiki/1941569)

如果需要在 Hyperf 中检测 HTTP Server 中的内存泄漏，可以在 `config/autoload/middlewares.php` 添加一个全局中间件：

```php
<?php

return [
    'http' => [
        Hyperf\SwooleTracker\Middleware\HookMallocMiddleware::class,
    ],
];
```

其他类型 Server 同理。
