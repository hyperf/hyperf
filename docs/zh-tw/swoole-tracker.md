# Swoole Tracker

[Swoole Tracker](https://www.swoole-cloud.com/tracker.html)是 Swoole 官方推出的一整套企業級包括 PHP 和  Swoole 分析除錯工具以及應用效能管理（APM）平臺，針對常規的 FPM 和 Swoole 常駐程序的業務，提供全面的效能監控、分析和除錯的解決方案。（曾命名：Swoole Enterprise）

Swoole Tracker 能夠幫助企業自動分析並彙總統計關鍵系統呼叫並智慧準確的定位到具體的 PHP 業務程式碼，實現業務應用效能最優化、強大的除錯工具鏈為企業業務保駕護航、提高 IT 生產效率。

- 時刻掌握應用架構模型
> 自動發現應用依賴拓撲結構和展示，時刻掌握應用的架構模型

- 分散式跨應用鏈路追蹤
> 支援無侵入的分散式跨應用鏈路追蹤，讓每個請求一目瞭然，全面支援協程/非協程環境，資料實時視覺化

- 全面分析報告服務狀況
> 各種維度統計服務上報的呼叫資訊， 比如總流量、平均耗時、超時率等，並全面分析報告服務狀況

- 擁有強大的除錯工具鏈
> 本系統支援遠端除錯，可在系統後臺遠端開啟檢測記憶體洩漏、阻塞檢測、程式碼效能分析和檢視呼叫棧；也支援手動埋點進行除錯，後臺統一檢視結果

- 同時支援 FPM 和 Swoole
> 完美支援 PHP-FPM 環境，不僅限於在 Swoole 中使用

- 完善的系統監控
> 支援完善的系統監控，零成本部署，監控機器的 CPU、記憶體、網路、磁碟等資源，可以很方便的整合到現有報警系統

- 一鍵安裝和零成本接入
> 規避與減小整體投資風險，本系統的客戶端提供指令碼可一鍵部署，服務端可在 Docker 環境中執行，簡單快捷

- 提高各部門生產效率
> 在複雜系統中追蹤服務及程式碼層級效能瓶頸，幫助 IT、開發等部門提升工作效率，將重點聚焦在核心工作中

## 安裝

### 安裝擴充套件

註冊完賬戶後，進入[控制檯](https://www.swoole-cloud.com/dashboard/catdemo/)，並申請試用，下載對應客戶端。

相關文件，請移步 [試用文件](https://www.kancloud.cn/swoole-inc/ee-base-wiki/1214079) 或 [詳細文件](https://www.kancloud.cn/swoole-inc/ee-help-wiki/1213080) 

> 具體文件地址，以從控制檯下載的對應客戶端中展示的為準。

將客戶端中的所有檔案以及以下兩個檔案複製到專案目錄 `.build` 中

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
apm.enable=1           #開啟總開關
apm.sampling_rate=100  #取樣率 例如：100%

# 開啟記憶體洩漏檢測時需要新增
apm.enable_memcheck=1  #開啟記憶體洩漏檢測 預設0 關閉狀態
```

然後將下面的 `Dockerfile` 複製到專案根目錄中。

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

# 這裡的地址，以客戶端中顯示的為準
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

### 不依賴元件

`Swoole Tracker` 的 `v2.5.0` 以上版本支援自動生成應用名稱並建立應用，無需修改任何程式碼。

如果使用 `Swoole` 的 `HttpServer` 那麼生成的應用名稱為`ip:port`

如果使用 `Swoole` 其他的 `Server` 那麼生成的應用名稱為`ip(hostname):port`

即安裝好 `swoole_tracker` 擴充套件之後就可以正常使用 `Swoole Tracker` 的功能

### 依賴元件

當你需要自定義應用名稱時則需要安裝元件，使用 `Composer` 安裝：

```bash
composer require hyperf/swoole-tracker
```

安裝完成後在 `config/autoload/middlewares.php` 配置檔案中註冊 `Hyperf\SwooleTracker\Middleware\HttpServerMiddleware` 中介軟體即可，如下：

```php
<?php

return [
    'http' => [
        Hyperf\SwooleTracker\Middleware\HttpServerMiddleware::class
    ],
];
```
