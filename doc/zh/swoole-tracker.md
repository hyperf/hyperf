# Swoole Tracker

[Swoole Tracker](https://www.swoole-cloud.com/tracker.html)是 Swoole 官方推出的一整套企业级包括 PHP 和  Swoole 分析调试工具以及应用性能管理（APM）平台，针对常规的 FPM 和 Swoole 常驻进程的业务，提供全面的性能监控、分析和调试的解决方案。（曾命名：Swoole Enterprise）

Swoole Tracker 能够帮助企业自动分析并汇总统计关键系统调用并智能准确的定位到具体的 PHP 业务代码，实现业务应用性能最优化、强大的调试工具链为企业业务保驾护航、提高 IT 生产效率。

- 时刻掌握应用架构模型
> 自动发现应用依赖拓扑结构和展示，时刻掌握应用的架构模型

- 分布式跨应用链路追踪
> 支持无侵入的分布式跨应用链路追踪，让每个请求一目了然，全面支持协程/非协程环境，数据实时可视化

- 全面分析报告服务状况
> 各种维度统计服务上报的调用信息， 比如总流量、平均耗时、超时率等，并全面分析报告服务状况

- 拥有强大的调试工具链
> 本系统支持远程调试，可在系统后台远程开启检测内存泄漏、阻塞检测、代码性能分析和查看调用栈；也支持手动埋点进行调试，后台统一查看结果

- 同时支持FPM和Swoole
> 完美支持PHP-FPM环境，不仅限于在Swoole中使用

- 完善的系统监控
> 支持完善的系统监控，零成本部署，监控机器的CPU、内存、网络、磁盘等资源，可以很方便的集成到现有报警系统

- 一键安装和零成本接入
> 规避与减小整体投资风险，本系统的客户端提供脚本可一键部署，服务端可在Docker环境中运行，简单快捷

- 提高各部门生产效率
> 在复杂系统中追踪服务及代码层级性能瓶颈，帮助IT、开发等部门提升工作效率，将重点聚焦在核心工作中

## 安装

### 安装扩展

注册完账户后，进入[控制台](https://www.swoole-cloud.com/dashboard/catdemo/)，并申请试用，下载对应客户端。

相关文档，请移步 [试用文档](https://www.kancloud.cn/swoole-inc/ee-base-wiki/1214079) 或 [详细文档](https://www.kancloud.cn/swoole-inc/ee-help-wiki/1213080) 

> 具体文档地址，以从控制台下载的对应客户端中展示的为准。

将客户端中的所有文件以及以下两个文件复制到项目目录 `.build` 中

1. `entrypoint.sh`

```bash
#!/usr/bin/env bash

/opt/swoole/script/php/swoole_php /opt/swoole/node-agent/src/node.php &

php /opt/www/bin/hyperf.php start

```

2. `swoole-tracker.ini`

```bash
[swoole_tracker]
extension=/opt/swoole_tracker.so
apm.enable=1           #打开总开关
apm.sampling_rate=100  #采样率 例如：100%

# 开启内存泄漏检测时需要添加
apm.enable_memcheck=1  #开启内存泄漏检测 默认0 关闭状态
```

然后将下面的 `Dockerfile` 复制到项目根目录中。

```dockerfile
FROM hyperf/hyperf:7.2-alpine-cli
LABEL maintainer="Hyperf Developers <group@hyperf.io>" version="1.0" license="MIT"

##
# ---------- env settings ----------
##
# --build-arg timezone=Asia/Shanghai
ARG timezone

ENV TIMEZONE=${timezone:-"Asia/Shanghai"} \
    COMPOSER_VERSION=1.8.6 \
    APP_ENV=prod

RUN set -ex \
    && apk update \
    # install composer
    && cd /tmp \
    && wget https://github.com/composer/composer/releases/download/${COMPOSER_VERSION}/composer.phar \
    && chmod u+x composer.phar \
    && mv composer.phar /usr/local/bin/composer \
    # show php version and extensions
    && php -v \
    && php -m \
    #  ---------- some config ----------
    && cd /etc/php7 \
    # - config PHP
    && { \
        echo "upload_max_filesize=100M"; \
        echo "post_max_size=108M"; \
        echo "memory_limit=1024M"; \
        echo "date.timezone=${TIMEZONE}"; \
    } | tee conf.d/99-overrides.ini \
    # - config timezone
    && ln -sf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime \
    && echo "${TIMEZONE}" > /etc/timezone \
    # ---------- clear works ----------
    && rm -rf /var/cache/apk/* /tmp/* /usr/share/man \
    && echo -e "\033[42;37m Build Completed :).\033[0m\n"

COPY . /opt/www
WORKDIR /opt/www/.build

# 这里的地址，以客户端中显示的为准
RUN ./deploy_env.sh www.swoole-cloud.com \
    && chmod 755 entrypoint.sh \
    && cp swoole_tracker72.so /opt/swoole_tracker.so \
    && cp swoole-tracker.ini /etc/php7/conf.d/swoole-tracker.ini \
    && php -m

WORKDIR /opt/www

RUN composer install --no-dev \
    && composer dump-autoload -o \
    && php /opt/www/bin/hyperf.php di:init-proxy

EXPOSE 9501

ENTRYPOINT ["sh", ".build/entrypoint.sh"]
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
