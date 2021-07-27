# 常見問題

## Swoole 短名未關閉

```
[ERROR] Swoole short name have to disable before start server, please set swoole.use_shortname = 'Off' into your php.ini.
```

您需要在您的 php.ini 配置檔案增加 `swoole.use_shortname = 'Off'` 配置項

> 注意該配置必須於 php.ini 內配置，無法通過 ini_set() 函式來重寫

當然，也可以通過以下的命令來啟動服務，在執行 PHP 命令時關閉掉 Swoole 短名功能

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

## 使用 Swoole 4.5 版本和 view 元件時訪問接口出現 404

使用 Swoole 4.5 版本和 view 元件如果出現介面 404 的問題，可以嘗試刪除 `config/autoload/server.php` 檔案中的 `static_handler_locations` 配置項。

此配置下的路徑都會被認為是靜態檔案路由，所以如果配置了`/`，就會導致所有介面都會被認為是檔案路徑，導致介面 404。

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

PHP 預設的 `memory_limit` 只有 `128M`，因為 `Hyperf` 使用了 `BetterReflection`，不使用掃描快取時，會消耗大量記憶體，所以可能會出現記憶體不夠的情況。

我們可以使用 `php -dmemory_limit=-1 bin/hyperf.php start` 執行, 或者修改 `php.ini` 配置檔案

```
# 檢視 php.ini 配置檔案位置
php --ini

# 修改 memory_limit 配置
memory_limit=-1
```

## PHP 7.3 版本對 DI 的相容性有所下降

在 `2.0` - `2.1` 版本時，為了實現 `AOP` 作用於非 `DI` 管理的物件（如 `new` 關鍵詞例項化的物件時），底層實現採用了 `BetterReflection` 元件來實現相關功能，帶來新的程式設計體驗的同時，也帶來了一些很難攻克的問題，如下:

- 無掃描快取時專案啟動很慢
- 特殊場景下 `Inject` 和 `Value` 不生效
- `BetterReflection` 尚未支援 PHP 8 (截止 2.2 發版時)

在新的版本里，棄用了 `BetterReflection` 的應用，採用了 `子程序掃描` 的方式來解決以上這些痛點，但在低版本的 `PHP` 中也有一些不相容的情況：

使用 `PHP 7.3` 啟動應用後遇到類似如下錯誤：

```bash
PHP Fatal error:  Interface 'Hyperf\Signal\SignalHandlerInterface' not found in vendor/hyperf/process/src/Handler/ProcessStopHandler.php on line 17
```

此問題是由於在 `PHP 7.3` 中通過 `子程序掃描` 的方式去獲取反射，在某個類中實現了一個不存在的 `Interface` ，就會導致丟擲 `Interface not found` 的異常，而高版本的 `PHP` 則不會。

解決方法為建立對應的 `Interface` 並正常引入。上文中的報錯解決方法為安裝 `hyperf/signal` 元件即可。

> 當然，最好還是可以升級到 7.4 或者 8.0 版本

```bash
composer require hyperf/signal
```
