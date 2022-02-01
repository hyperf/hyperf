# Session 會話管理

HTTP 是一種無狀態協議，即伺服器不保留與客戶交易時的任何狀態，所以當我們在開發 HTTP Server 應用時，我們通常會通過 Session 來實現多個請求之間使用者資料的共享。您可通過 [hyperf/session](https://github.com/hyperf/session) 來實現 Session 的功能。Session 元件當前僅適配了兩種儲存驅動，分別為 `檔案` 和 `Redis`，預設為 `檔案` 驅動，在生產環境下，我們強烈建議您使用 `Redis` 來作為儲存驅動，這樣效能更好也更符合叢集架構下的使用。

# 安裝

```bash
composer require hyperf/session
```

# 配置

Session 元件的配置儲存於 `config/autoload/session.php` 檔案中，如檔案不存在，您可通過 `php bin/hyperf.php vendor:publish hyperf/session` 命令來將 Session 元件的配置檔案釋出到 Skeleton 去。

## 配置 Session 中介軟體

在使用 Session 之前，您需要將 `Hyperf\Session\Middleware\SessionMiddleware` 中介軟體配置為 HTTP Server 的全域性中介軟體，這樣元件才能介入到請求流程進行對應的處理，`config/autoload/middlewares.php` 配置檔案示例如下：

```php
<?php

return [
    // 這裡的 http 對應預設的 server name，如您需要在其它 server 上使用 Session，需要對應的配置全域性中介軟體
    'http' => [
        \Hyperf\Session\Middleware\SessionMiddleware::class,
    ],
];
```

## 配置儲存驅動

通過更改配置檔案中的 `handler` 配置修改不同的 Session 儲存驅動，而對應 Handler 的具體配置項則由 `options` 內不同的配置項決定。

### 使用檔案儲存驅動

> 檔案儲存驅動是預設的儲存驅動，但建議生產環境下使用 Redis 驅動

當 `handler` 的值為 `Hyperf\Session\Handler\FileHandler` 時則表明使用 `檔案` 儲存驅動，所有的 Session 資料檔案都會被生成並儲存在 `options.path` 配置值對應的資料夾中，預設配置的資料夾為根目錄下的 `runtime/session` 資料夾內。

### 使用 Redis 驅動

在使用 `Redis` 儲存驅動之前，您需要安裝 [hyperf/redis](https://github.com/hyperf/redis) 元件。當 `handler` 的值為 `Hyperf\Session\Handler\RedisHandler` 時則表明使用 `Redis` 儲存驅動。您可以通過配置 `options.connection` 配置值來調整驅動要使用的 `Redis` 連線，這裡的連線與 [hyperf/redis](https://github.com/hyperf/redis) 元件的 `config/autoload/redis.php` 配置內的 key 命名匹配，

# 使用

## 獲得 Session 物件

獲得 Session 物件可通過注入 `Hyperf\Contract\SessionInterface`，即可呼叫介面定義的方法來實現使用：

```php
<?php

namespace App\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Contract\SessionInterface;

class IndexController
{
    #[Inject]
    private SessionInterface $session;

    public function index()
    {
        // 直接通過 $this->session 來使用
    } 
}
```

## 儲存資料

當您希望儲存資料到 Session 中去，您可通過呼叫 `set(string $name, $value): void` 方法來實現：

```php
<?php

$this->session->set('foo', 'bar');
```

## 獲取資料

當您希望從 Session 中獲取資料，您可通過呼叫 `get(string $name, $default = null)` 方法來實現：

```php
<?php

$this->session->get('foo', $default = null);
```

### 獲取所有資料

您可通過呼叫 `all(): array` 方法一次性從 Session 中獲得所有的已儲存資料：

```php
<?php

$data = $this->session->all();
```

## 判斷 Session 中是否存在某個值

要確定 Session 中是否存在某個值，可以使用 `has(string $name): bool` 方法。如果該值存在且不為 null，那麼 `has` 方法會返回 `true`：

```php
<?php

if ($this->session->has('foo')) {
    //
}
```

## 獲取並刪除一條資料

通過呼叫 `remove(string $name)` 方法可以只使用一個方法就從 Session 中獲取並刪除一條資料：

```php
<?php

$data = $this->session->remove('foo');
```

## 刪除一條或多條資料

通過呼叫 `forget(string|array $name): void` 方法可以只使用一個方法就從 Session 中刪除一條或多條資料，當傳遞字串時，表示僅刪除一條資料，當傳遞一個 key 字串陣列時，表示刪除多條資料：

```php
<?php

$this->session->forget('foo');
$this->session->forget(['foo', 'bar']);
```

## 清空當前 Session 資料

當您希望清空當前 Session 裡的所有資料，您可通過呼叫 `clear(): void` 方法來實現：

```php
<?php

$this->session->clear();
```

## 獲取當前的 Session ID

當您希望獲取當前帶 Session ID 去自行處理一些邏輯時，您可通過呼叫 `getId(): string` 方法來獲取當前的 Session ID：

```php
<?php

$sessionId = $this->session->getId();
```

