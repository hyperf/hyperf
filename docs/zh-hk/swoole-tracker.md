# Swoole Tracker

[Swoole Tracker](https://business.swoole.com/tracker/index)是 Swoole 官方推出的一整套企業級包括 PHP 和  Swoole 分析調試工具以及應用性能管理（APM）平台，針對常規的 FPM 和 Swoole 常駐進程的業務，提供全面的性能監控、分析和調試的解決方案。（曾命名：Swoole Enterprise）

Swoole Tracker 能夠幫助企業自動分析並彙總統計關鍵系統調用並智能準確的定位到具體的 PHP 業務代碼，實現業務應用性能最優化、強大的調試工具鏈為企業業務保駕護航、提高 IT 生產效率。

- 時刻掌握應用架構模型
> 自動發現應用依賴拓撲結構和展示，時刻掌握應用的架構模型

- 分佈式跨應用鏈路追蹤
> 支持無侵入的分佈式跨應用鏈路追蹤，讓每個請求一目瞭然，全面支持協程/非協程環境，數據實時可視化

- 全面分析報告服務狀況
> 各種維度統計服務上報的調用信息， 比如總流量、平均耗時、超時率等，並全面分析報告服務狀況

- 擁有強大的調試工具鏈
> 本系統支持遠程調試，可在系統後台遠程開啟檢測內存泄漏、阻塞檢測、代碼性能分析和查看調用棧；也支持手動埋點進行調試，後台統一查看結果

- 同時支持 FPM 和 Swoole
> 完美支持 PHP-FPM 環境，不僅限於在 Swoole 中使用

- 完善的系統監控
> 支持完善的系統監控，零成本部署，監控機器的 CPU、內存、網絡、磁盤等資源，可以很方便的集成到現有報警系統

- 一鍵安裝和零成本接入
> 規避與減小整體投資風險，本系統的客户端提供腳本可一鍵部署，服務端可在 Docker 環境中運行，簡單快捷

- 提高各部門生產效率
> 在複雜系統中追蹤服務及代碼層級性能瓶頸，幫助 IT、開發等部門提升工作效率，將重點聚焦在核心工作中

## 安裝

### 安裝擴展

註冊完賬户後，進入[控制枱](https://business.swoole.com/SwooleTracker/catdemo)，並申請試用，下載對應的安裝腳本。

相關文檔，請移步 [試用文檔](https://www.kancloud.cn/swoole-inc/ee-base-wiki/1214079) 或 [詳細文檔](https://www.kancloud.cn/swoole-inc/ee-help-wiki/1213080) 

將腳本以及以下兩個文件複製到項目目錄 `.build` 中

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
;打開總開關
apm.enable=1
;採樣率 例如：100%
apm.sampling_rate=100
;開啟內存泄漏檢測時添加 默認0 關閉狀態
apm.enable_memcheck=1

;Tracker從v3.3.0版本開始修改為了Zend擴展
zend_extension=swoole_tracker.so
tracker.enable=1
tracker.sampling_rate=100
tracker.enable_memcheck=1
```

然後將下面的 `Dockerfile` 複製到項目根目錄中。

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

### 不依賴組件

`Swoole Tracker` 的 `v2.5.0` 以上版本支持自動生成應用名稱並創建應用，無需修改任何代碼。

如果使用 `Swoole` 的 `HttpServer` 那麼生成的應用名稱為`ip:port`

如果使用 `Swoole` 其他的 `Server` 那麼生成的應用名稱為`ip(hostname):port`

即安裝好 `swoole_tracker` 擴展之後就可以正常使用 `Swoole Tracker` 的功能

### 依賴組件

當你需要自定義應用名稱時則需要安裝組件，使用 `Composer` 安裝：

```bash
composer require hyperf/swoole-tracker
```

安裝完成後在 `config/autoload/middlewares.php` 配置文件中註冊 `Hyperf\SwooleTracker\Middleware\HttpServerMiddleware` 中間件即可，如下：

```php
<?php

return [
    'http' => [
        Hyperf\SwooleTracker\Middleware\HttpServerMiddleware::class
    ],
];
```

若使用 `jsonrpc-http` 協議實現了 `RPC` 服務，則還需要在 `config/autoload/aspects.php` 配置以下 `Aspect`：

```php
<?php

return [
    Hyperf\SwooleTracker\Aspect\CoroutineHandlerAspect::class,
];
```

## 免費內存泄漏檢測工具

Swoole Tracker 本是一款商業產品，擁有進行內存泄漏檢測的能力，不過 Swoole Tracker 把內存泄漏檢測的功能完全免費給 PHP 社區使用，完善 PHP 生態，回饋社區，下面將概述它的具體用法。

1. 前往 [Swoole Tracker 官網](https://business.swoole.com/SwooleTracker/download/) 下載最新的 Swoole Tracker 擴展；

2. 和上文添加擴展相同，再加入一行配置：

```ini
;Leak檢測開關
apm.enable_malloc_hook=1
```

!> 注意：不要在 composer 安裝依賴時開啟；不要在生成代理類緩存時開啟。

3. 根據自己的業務，在 Swoole 的 onReceive 或者 onRequest 事件開頭加上 `trackerHookMalloc()` 調用：

```php
$http->on('request', function ($request, $response) {
    trackerHookMalloc();
    $response->end("<h1>Hello Swoole. #".rand(1000, 9999)."</h1>");
});
```

每次調用結束後（第一次調用不會被記錄），都會生成一個泄漏的信息到 `/tmp/trackerleak` 日誌中，我們可以在 Cli 命令行調用 `trackerAnalyzeLeak()` 函數即可分析泄漏日誌，生成泄漏報告

```shell
php -r "trackerAnalyzeLeak();"
```

下面是泄漏報告的格式：

沒有內存泄漏的情況：

```
[16916 (Loop 5)] ✅ Nice!! No Leak Were Detected In This Loop
```

其中 `16916` 表示進程 id，`Loop 5`表示第 5 次調用主函數生成的泄漏信息

有確定的內存泄漏：

```
[24265 (Loop 8)] /tests/mem_leak/http_server.php:125 => [12928]
[24265 (Loop 8)] /tests/mem_leak/http_server.php:129 => [12928]
[24265 (Loop 8)] ❌ This Loop TotalLeak: [25216]
```

表示第 8 次調用 `http_server.php` 的 125 行和 129 行，分別泄漏了 12928 字節內存，總共泄漏了 25216 字節內存。

通過調用 `trackerCleanLeak()` 可以清除泄漏日誌，重新開始。[瞭解更多內存檢測工具使用細節](https://www.kancloud.cn/swoole-inc/ee-help-wiki/1941569)

如果需要在 Hyperf 中檢測 HTTP Server 中的內存泄漏，可以在 `config/autoload/middlewares.php` 添加一個全局中間件：

```php
<?php

return [
    'http' => [
        Hyperf\SwooleTracker\Middleware\HookMallocMiddleware::class,
    ],
];
```

其他類型 Server 同理。
