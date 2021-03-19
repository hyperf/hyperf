# Swoole Tracker

[Swoole Tracker](https://www.swoole-cloud.com/tracker.html)是 Swoole 官方推出的一整套企业级包括 PHP 和  Swoole 分析调试工具以及应用性能管理（APM）平台，针对常规的 FPM 和 Swoole 常驻进程的业务，提供全面的性能监控、分析和调试的解决方案。（曾命名：Swoole Enterprise）

- 时刻掌握应用架构模型
- 分布式跨应用链路追踪
- 完善的系统监控
- 零成本接入
- 全面分析报告服务状况

## Installation

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
