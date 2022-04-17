# Swoole Tracker

[Swoole Tracker](https://business.swoole.com/tracker/index)是 Swoole 官方推出的一整套企業級包括 PHP 和  Swoole 分析除錯工具以及應用效能管理（APM）平臺，針對常規的 FPM 和 Swoole 常駐程序的業務，提供全面的效能監控、分析和除錯的解決方案。（曾命名：Swoole Enterprise）

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

註冊完賬戶後，進入[控制檯](https://business.swoole.com/SwooleTracker/catdemo)，並申請試用，下載對應的安裝指令碼。

相關文件，請移步 [試用文件](https://www.kancloud.cn/swoole-inc/ee-base-wiki/1214079) 或 [詳細文件](https://www.kancloud.cn/swoole-inc/ee-help-wiki/1213080) 

將指令碼以及以下兩個檔案複製到專案目錄 `.build` 中

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
;開啟總開關
apm.enable=1
;取樣率 例如：100%
apm.sampling_rate=100
;開啟記憶體洩漏檢測時新增 預設0 關閉狀態
apm.enable_memcheck=1

;Tracker從v3.3.0版本開始修改為了Zend擴充套件
zend_extension=swoole_tracker.so
tracker.enable=1
tracker.sampling_rate=100
tracker.enable_memcheck=1
```

然後將下面的 `Dockerfile` 複製到專案根目錄中。

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

若使用 `jsonrpc-http` 協議實現了 `RPC` 服務，則還需要在 `config/autoload/aspects.php` 配置以下 `Aspect`：

```php
<?php

return [
    Hyperf\SwooleTracker\Aspect\CoroutineHandlerAspect::class,
];
```

## 免費記憶體洩漏檢測工具

Swoole Tracker 本是一款商業產品，擁有進行記憶體洩漏檢測的能力，不過 Swoole Tracker 把記憶體洩漏檢測的功能完全免費給 PHP 社群使用，完善 PHP 生態，回饋社群，下面將概述它的具體用法。

1. 前往 [Swoole Tracker 官網](https://business.swoole.com/SwooleTracker/download/) 下載最新的 Swoole Tracker 擴充套件；

2. 和上文新增擴充套件相同，再加入一行配置：

```ini
;Leak檢測開關
apm.enable_malloc_hook=1
```

!> 注意：不要在 composer 安裝依賴時開啟；不要在生成代理類快取時開啟。

3. 根據自己的業務，在 Swoole 的 onReceive 或者 onRequest 事件開頭加上 `trackerHookMalloc()` 呼叫：

```php
$http->on('request', function ($request, $response) {
    trackerHookMalloc();
    $response->end("<h1>Hello Swoole. #".rand(1000, 9999)."</h1>");
});
```

每次呼叫結束後（第一次呼叫不會被記錄），都會生成一個洩漏的資訊到 `/tmp/trackerleak` 日誌中，我們可以在 Cli 命令列呼叫 `trackerAnalyzeLeak()` 函式即可分析洩漏日誌，生成洩漏報告

```shell
php -r "trackerAnalyzeLeak();"
```

下面是洩漏報告的格式：

沒有記憶體洩漏的情況：

```
[16916 (Loop 5)] ✅ Nice!! No Leak Were Detected In This Loop
```

其中 `16916` 表示程序 id，`Loop 5`表示第 5 次呼叫主函式生成的洩漏資訊

有確定的記憶體洩漏：

```
[24265 (Loop 8)] /tests/mem_leak/http_server.php:125 => [12928]
[24265 (Loop 8)] /tests/mem_leak/http_server.php:129 => [12928]
[24265 (Loop 8)] ❌ This Loop TotalLeak: [25216]
```

表示第 8 次呼叫 `http_server.php` 的 125 行和 129 行，分別洩漏了 12928 位元組記憶體，總共洩漏了 25216 位元組記憶體。

通過呼叫 `trackerCleanLeak()` 可以清除洩漏日誌，重新開始。[瞭解更多記憶體檢測工具使用細節](https://www.kancloud.cn/swoole-inc/ee-help-wiki/1941569)

如果需要在 Hyperf 中檢測 HTTP Server 中的記憶體洩漏，可以在 `config/autoload/middlewares.php` 新增一個全域性中介軟體：

```php
<?php

return [
    'http' => [
        Hyperf\SwooleTracker\Middleware\HookMallocMiddleware::class,
    ],
];
```

其他型別 Server 同理。
