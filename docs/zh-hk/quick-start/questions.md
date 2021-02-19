# 常見問題

## `Inject` 或 `Value` 註解不生效

`2.0` 使用了構造函數中注入 `Inject` 和 `Value` 的功能，以下兩種場景，可能會導致注入失效，請注意使用。

1. 原類沒有使用 `Inject` 或 `Value`，但父類使用了 `Inject` 或 `Value`，且原類寫了構造函數，同時又沒有調用父類構造函數的情況。

這樣就會導致原類不會生成代理類，而實例化的時候又調用了自身的構造函數，故沒辦法執行到父類的構造函數。
所以父類代理類中的方法 `__handlePropertyHandler` 就不會執行，那麼 `Inject` 或 `Value` 註解就不會生效。

```php
class ParentClass {
    /**
     * @Inject
     * @var Service
     */
    protected $value;
}

class Origin extends ParentClass
{
    public function __construct() {}
}
```

2. 原類沒有使用 `Inject` 或 `Value`，但 `Trait` 中使用了 `Inject` 或 `Value`。

這樣就會導致原類不會生成代理類，故沒辦法執行構造函數裏的 `__handlePropertyHandler`，所以 `Trait` 的 `Inject` 或 `Value` 註解就不會生效。

```php
trait OriginTrait {
    /**
     * @Inject
     * @var Service
     */
    protected $value;
}

class Origin
{
    use OriginTrait;
}
```

基於上述兩種情況，可見 `原類` 是否生成代理類至關重要，所以，如果使用了帶有 `Inject` 或 `Value` 的 `Trait` 和 `父類` 時，給原類添加一個 `Inject`，即可解決上述兩種情況。

```php

use Hyperf\Contract\StdoutLoggerInterface;

trait OriginTrait {
    /**
     * @Inject
     * @var Service
     */
    protected $trait;
}

class ParentClass {
    /**
     * @Inject
     * @var Service
     */
    protected $value;
}

class Origin extends ParentClass
{
    use OriginTrait;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    protected $logger;
}
```

## Swoole 短名未關閉

```
[ERROR] Swoole short name have to disable before start server, please set swoole.use_shortname = 'Off' into your php.ini.
```

您需要在您的 php.ini 配置文件增加 `swoole.use_shortname = 'Off'` 配置項

> 注意該配置必須於 php.ini 內配置，無法通過 ini_set() 函數來重寫

當然，也可以通過以下的命令來啟動服務，在執行 PHP 命令時關閉掉 Swoole 短名功能

```
php -d swoole.use_shortname=Off bin/hyperf.php start
```

## 異步隊列消息丟失

如果在使用 `async-queue` 組件時，發現 `handle` 中的方法沒有執行，請先檢查以下幾種情況：

1. `Redis` 是否與其他人共用，消息被其他人消費走
2. 本地進程是否存在殘餘，被其他進程消費掉

以下提供萬無一失的解決辦法：

1. killall php
2. 修改 `async-queue` 配置 `channel`

## 使用 AMQP 組件報 `Swoole\Error: API must be called in the coroutine` 錯誤

可以在 `config/autoload/amqp.php` 配置文件中將 `params.close_on_destruct` 改為 `false` 即可。

## 使用 Swoole 4.5 版本和 view 組件時訪問接口出現 404

使用 Swoole 4.5 版本和 view 組件如果出現接口 404 的問題，可以嘗試刪除 `config/autoload/server.php` 文件中的 `static_handler_locations` 配置項。

此配置下的路徑都會被認為是靜態文件路由，所以如果配置了`/`，就會導致所有接口都會被認為是文件路徑，導致接口 404。

## 代碼不生效

當碰到修改後的代碼不生效的問題，請執行以下命令

```bash
composer dump-autoload -o
```

開發階段，請不要設置 `scan_cacheable` 為 `true`，它會導致 `收集器緩存` 存在時，不會再次掃描文件。另外，官方骨架包中的 `Dockerfile` 是默認開啟這個配置的，`Docker` 環境下開發的同學，請注意這裏。

> 當環境變量存在 SCAN_CACHEABLE 時，.env 中無法修改這個配置。

## 語法錯誤導致服務無法啟動

當項目啟動時，拋出類似於以下錯誤時

```
Fatal error: Uncaught PhpParser\Error: Syntax error, unexpected T_STRING on line 27 in vendor/nikic/php-parser/lib/PhpParser/ParserAbstract.php:315
```

可以執行腳本 `composer analyse`，對項目進行靜態檢測，便可以找到出現問題的代碼段。

此問題通常是由於 [zircote/swagger](https://github.com/zircote/swagger-php) 的 3.0.5 版本更新導致, 詳情請見 [#834](https://github.com/zircote/swagger-php/issues/834) 。
如果安裝了 [hyperf/swagger](https://github.com/hyperf/swagger) 建議將 [zircote/swagger](https://github.com/zircote/swagger-php) 的版本鎖定在 3.0.4

## 內存限制太小導致項目無法運行

PHP 默認的 `memory_limit` 只有 `128M`，因為 `Hyperf` 使用了 `BetterReflection`，不使用掃描緩存時，會消耗大量內存，所以可能會出現內存不夠的情況。

我們可以使用 `php -dmemory_limit=-1 bin/hyperf.php start` 運行, 或者修改 `php.ini` 配置文件

```
# 查看 php.ini 配置文件位置
php --ini

# 修改 memory_limit 配置
memory_limit=-1
```
