# 程式設計須知

這裡收集各種透過 Hyperf 程式設計前應該知曉的知識點或內容點。

## 不能透過全域性變數獲取屬性引數

在 `PHP-FPM` 下可以透過全域性變數獲取到請求的引數，伺服器的引數等，在 `Hyperf` 和 `Swoole` 內，都 **無法** 透過 `$_GET/$_POST/$_REQUEST/$_SESSION/$_COOKIE/$_SERVER`等`$_`開頭的變數獲取到任何屬性引數。

## 透過容器獲取的類都是單例

透過依賴注入容器獲取的都是程序內持久化的，是多個協程共享的，所以不能包含任何的請求唯一的資料或協程唯一的資料，這型別的資料都透過協程上下文去處理，具體請仔細閱讀 [依賴注入](zh-tw/di.md) 和 [協程](zh-tw/coroutine.md) 章節。

## 專案部署

> 官方的 Dockerfile 已經完成了以下操作。

線上程式碼部署時，請務必開啟 `scan_cacheable`。

開啟此配置後，首次掃描時會生成代理類和註解快取，再次啟動時，則可以直接使用快取，極大最佳化記憶體使用率和啟動速度。因為跳過了掃描階段，所以會依賴 `Composer Class Map`，故我們必須要執行 `--optimize-autoloader` 最佳化索引。

綜上，線上更新程式碼，重啟專案前，需要執行以下命令

```bash
# 最佳化 Composer 索引
composer dump-autoload -o
# 生成代理類和註解快取
php bin/hyperf.php
```


## 避免在魔術方法中切換協程

> __call __callStatic 除外

儘量避免在 `__get` `__set` 和 `__isset` 中切換協程，因為可能會出現不符合預期的情況

```php
<?php

require_once 'vendor/autoload.php';
Swoole\Coroutine::set(['hook_flags' => SWOOLE_HOOK_ALL]);

class Foo
{
    public function __get(string $name)
    {
        sleep(1);
        return $name;
    }

    public function __set(string $name, mixed $value)
    {
        sleep(1);
        var_dump($name, $value);
    }

    public function __isset(string $name): bool
    {
        sleep(1);
        var_dump($name);
        return true;
    }
}

$foo = new Foo();
go(static function () use ($foo) {
    var_dump(isset($foo->xxx));
});

go(static function () use ($foo) {
    var_dump(isset($foo->xxx));
});

\Swoole\Event::wait();

```

當我們執行上述程式碼時，會返回以下結果

```shell
bool(false)
string(3) "xxx"
bool(true)
```
