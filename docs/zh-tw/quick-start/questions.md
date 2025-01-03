# 常見問題

## Swoole 短名未關閉

```
[ERROR] Swoole short function names must be disabled before the server starts, please set swoole.use_shortname = 'Off' in your php.ini.
```

您需要在您的 php.ini 配置檔案增加 `swoole.use_shortname = 'Off'` 配置項

> 注意該配置必須於 php.ini 內配置，無法透過 ini_set() 函式來重寫

當然，也可以透過以下的命令來啟動服務，在執行 PHP 命令時關閉掉 Swoole 短名功能

```
php -d swoole.use_shortname=Off bin/hyperf.php start
```

## 非同步佇列訊息丟失

如果在使用 `async-queue` 元件時，發現 `handle` 中的方法沒有執行，請先檢查以下幾種情況：

1. `Redis` 是否與其他人共用，訊息被其他人消費走
2. 本地程序是否存在殘餘，被其他程序消費掉

以下提供萬無一失的解決辦法：

1. killall php
2. 修改 `async-queue` 配置 `channel`

## 使用 AMQP 元件報 `Swoole\Error: API must be called in the coroutine` 錯誤

可以在 `config/autoload/amqp.php` 配置檔案中將 `params.close_on_destruct` 改為 `false` 即可。

## 程式碼不生效

當碰到修改後的程式碼不生效的問題，請執行以下命令

```bash
composer dump-autoload -o
```

開發階段，請不要設定 `scan_cacheable` 為 `true`，它會導致 `收集器快取` 存在時，不會再次掃描檔案。另外，官方骨架包中的 `Dockerfile` 是預設開啟這個配置的，`Docker` 環境下開發的同學，請注意這裡。

> 當環境變數存在 SCAN_CACHEABLE 時，.env 中無法修改這個配置。

## 語法錯誤導致服務無法啟動

當專案啟動時，丟擲類似於以下錯誤時

```
Fatal error: Uncaught PhpParser\Error: Syntax error, unexpected T_STRING on line 27 in vendor/nikic/php-parser/lib/PhpParser/ParserAbstract.php:315
```

可以執行指令碼 `composer analyse`，對專案進行靜態檢測，便可以找到出現問題的程式碼段。

此問題通常是由於 [zircote/swagger](https://github.com/zircote/swagger-php) 的 3.0.5 版本更新導致, 詳情請見 [#834](https://github.com/zircote/swagger-php/issues/834) 。
如果安裝了 [hyperf/swagger](https://github.com/hyperf/swagger) 建議將 [zircote/swagger](https://github.com/zircote/swagger-php) 的版本鎖定在 3.0.4

## 記憶體限制太小導致專案無法執行

PHP 預設的 `memory_limit` 只有 `128M`。

我們可以使用 `php -d memory_limit=-1 bin/hyperf.php start` 執行, 或者修改 `php.ini` 配置檔案

```
# 檢視 php.ini 配置檔案位置
php --ini

# 修改 memory_limit 配置
memory_limit=-1
```

## Trait 內使用 `#[Inject]` 注入報錯 `Error while injecting dependencies into ... No entry or class found ...`

若 Trait 透過 `#[Inject] @var` 注入屬性, 同時子類裡 `use` 了不同名稱空間的同名類, 會導致 Trait 裡類名被覆蓋，進而導致注入失效:

```php
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Di\Annotation\Inject;

trait TestTrait
{
    #[Inject]
    protected ResponseInterface $response;
}
```

如上 Trait 類注入 `Hyperf\HttpServer\Contract\ResponseInterface`, 若子類使用不同名稱空間的`ResponseInterface` 類, 如`use Psr\Http\Message\ResponseInterface`, 會導致 Trait 原類名被覆蓋:

```php
// use 同類名會覆蓋Trait
use Psr\Http\Message\ResponseInterface;

class IndexController
{
    use TestTrait;
}
// Error while injecting dependencies into App\Controller\IndexController: No entry or class found for 'Psr\Http\Message\ResponseInterface'
```

上述問題可以透過以下兩個方法解決:

- 子類透過 `as` 修改別名: `use Psr\Http\Message\ResponseInterface as PsrResponseInterface;`
- Trait 類`PHP7.4` 以上透過屬性型別限制: `protected ResponseInterface $response;`

## Grpc 擴充套件或未安裝 Pcntl 導致專案無法啟動

- v2.2 版本的註解掃描使用了 `pcntl` 擴充套件，所以請先確保您的 `PHP` 安裝了此擴充套件。

```shell
php --ri pcntl

pcntl

pcntl support => enabled
```

- 當開啟 `grpc` 的時候，需要新增 `grpc.enable_fork_support= 1;` 到 `php.ini` 中，以支援開啟子程序。

## HTTP Server 將 `open_websocket_protocol` 設定為 `false` 後啟動報錯：`Swoole\Server::start(): require onReceive callback`

1. 檢查 Swoole 是否編譯了 http2

```shell
php --ri swoole | grep http2
http2 => enabled
```

如果沒有，需要重新編譯 Swoole 並增加 `--enable-http2` 引數。

2. 檢查 server.php 檔案中 `open_http2_protocol` 選項是否為 `true`。

## Command 無法正常關閉

在 Command 中使用 AMQP 等多路複用技術後，會導致無法正常關閉，碰到這種情況只需要在執行邏輯最後增加以下程式碼即可。

```php
<?php
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coordinator\Constants;

CoordinatorManager::until(Constants::WORKER_EXIT)->resume();
```

## OSS 上傳元件報 iconv 錯誤

- fix aliyun oss wrong charset: https://github.com/aliyun/aliyun-oss-php-sdk/issues/101
- https://github.com/docker-library/php/issues/240#issuecomment-762438977
- https://github.com/docker-library/php/pull/1264

當使用 `aliyuncs/oss-sdk-php` 元件上傳時，會報 iconv 錯誤，可以嘗試使用以下方式規避：

使用 `hyperf/hyperf:8.0-alpine-v3.12-swoole` 映象時

```
RUN apk --no-cache --allow-untrusted --repository http://dl-cdn.alpinelinux.org/alpine/edge/community/ add gnu-libiconv=1.15-r2
ENV LD_PRELOAD /usr/lib/preloadable_libiconv.so
```

使用 `hyperf/hyperf:8.0-alpine-v3.13-swoole` 映象時

```dockerfile
RUN apk add --no-cache --repository http://dl-cdn.alpinelinux.org/alpine/v3.13/community/ gnu-libiconv=1.15-r3
ENV LD_PRELOAD /usr/lib/preloadable_libiconv.so php
```

## DI 收集失敗

在 DI 收集階段發生異常（如名稱空間錯誤等原因），可能產生以下格式日誌的輸出。

- 業務程式碼，排查日誌中路徑相關的檔案和類。
- 框架程式碼，提交 PR 或 Issue 反饋。
- 第三方元件，反饋給元件作者。

```bash
[ERROR] DI Reflection Manager collecting class reflections failed. 
File: xxxx.
Exception: xxxx
```

## 環境版本不一致導致服務無法啟動

當專案啟動時，丟擲類似如下錯誤時：

```bash
Hyperf\Engine\Channel::push(mixed $data, float $timeout = -1): bool must be compatible with Swoole\Coroutine\Channel::push($data, $timeout = -1)
```

此問題通常是由於實際執行時使用的 Swoole 版本和安裝框架/元件時使用的 Swoole 版本不一致導致。

使用和安裝時相同的 Swoole、PHP 版本即可解決。
