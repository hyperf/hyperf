# 常見問題

## Swoole 短名未關閉

```
[ERROR] Swoole short name have to disable before start server, please set swoole.use_shortname = 'Off' into your php.ini.
```

您需要在您的 php.ini 配置檔案增加 `swoole.use_shortname = 'Off'` 配置項

如果您使用的是 1.0.x 版本，這也可能是因為你按以下的方式設定了

```
// 在 1.0 系列版本下
// 這些都是錯誤的，注意 `大小寫` 和 `引號`
swoole.use_shortname = 'off'
swoole.use_shortname = off
swoole.use_shortname = Off
// 下面的才是正確的
swoole.use_shortname = 'Off'
```

> 注意該配置必須於 php.ini 內配置，無法通過 ini_set() 函式來重寫

當然，也可以通過以下的命令來啟動服務，在執行 PHP 命令時關閉掉 Swoole 短名功能

```
php -d swoole.use_shortname=Off bin/hyperf.php start
```

## 代理類快取

代理類快取一旦生成，將不會再重新覆蓋。所以當你修改了已經生成代理類的檔案時，需要手動清理。

代理類位置如下

```
runtime/container/proxy/
```

重新生成快取命令，新快取會覆蓋原目錄

```bash
vendor/bin/init-proxy.sh
```

刪除代理類快取

```bash
rm -rf ./runtime/container/proxy
```

所以單測命令可以使用以下代替：

```bash
vendor/bin/init-proxy.sh && composer test
```

同理，啟動命令可以使用以下代替

```bash
vendor/bin/init-proxy.sh && php bin/hyperf.php start
```

## 非同步佇列訊息丟失

如果在使用 `async-queue` 元件時，發現 `handle` 中的方法沒有執行，請先檢查以下幾種情況：

1. `Redis` 是否與其他人共用，訊息被其他人消費走
2. 本地程序是否存在殘餘，被其他程序消費掉

以下提供萬無一失的解決辦法：

1. killall php
2. 修改 `async-queue` 配置 `channel`

## 1.1.24 - 1.1.26 版本 SymfonyEventDispatcher 報錯

因為 `symfony/console` 預設使用的 `^4.2` 版本，而 `symfony/event-dispatcher` 的 `^4.3` 版本與 `<4.3` 版本不相容。

`hyperf/framework` 預設推薦使用 `^4.3` 版本的 `symfony/event-dispatcher`，就有一定概率導致實現上的衝突。

如果有類似的情況出現，可以嘗試以下操作

```
rm -rf vendor
rm -rf composer.lock
composer require "symfony/event-dispatcher:^4.3"
```

1.1.27 版本中，會在 `composer.json` 中新增以下配置，來處理這個問題。

```
    "conflict": {
        "symfony/event-dispatcher": "<4.3"
    },
```

## 使用 AMQP 元件報 `Swoole\Error: API must be called in the coroutine` 錯誤

可以在 `config/autoload/amqp.php` 配置檔案中將 `close_on_destruct` 改為 `false` 即可。

