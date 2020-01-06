# Swoole Dashboard

[dashboard](https://www.swoole-cloud.com/dashboard.html) 作为 `Swoole` 官方出品、更专一、更专业。

- 时刻掌握应用架构模型
- 分布式跨应用链路追踪
- 完善的系统监控
- 零成本接入
- 全面分析报告服务状况

## Installation

注册完账户后，进入[控制台](https://www.swoole-cloud.com/dashboard/catdemo/)，并申请试用，下载对应客户端。

相关文档，请移步 [试用文档](https://www.yuque.com/swoole-wiki/try) 或 [详细文档](https://www.yuque.com/swoole-wiki/dam5n7) 

> 具体文档地址，以从控制台下载的对应客户端中展示的为准。

将客户端中的所有文件以及以下两个文件复制到项目目录 `.build` 中，

entrypoint.sh

```bash
#!/usr/bin/env bash

/opt/swoole/script/php/swoole_php /opt/swoole/node-agent/src/node.php &

php /opt/www/bin/hyperf.php start

```

swoole-tracker.ini

```bash
[swoole_tracker]
extension=/opt/swoole_tracker.so
apm.enable=1           #打开总开关
apm.sampling_rate=100  #采样率 例如：100%

# 手动埋点时再添加
apm.enable_xhprof=1    #开启性能分析功能 默认0 即为关闭模式
apm.enable_memcheck=1  #开启内存泄漏检测 默认0 关闭
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

首先安装一下对应组件

```bash
composer require hyperf/swoole-dashboard dev-master
```

然后将以下 `Middleware` 写到 `middleware.php` 中。

```php
<?php

return [
    'http' => [
        Hyperf\SwooleDashboard\Middleware\HttpServerMiddleware::class
    ],
];

```

