# Session 會話管理

HTTP 是一種無狀態協議，即服務器不保留與客户交易時的任何狀態，所以當我們在開發 HTTP Server 應用時，我們通常會通過 Session 來實現多個請求之間用户數據的共享。您可通過 [hyperf/session](https://github.com/hyperf/session) 來實現 Session 的功能。Session 組件當前僅適配了兩種儲存驅動，分別為 `文件` 和 `Redis`，默認為 `文件` 驅動，在生產環境下，我們強烈建議您使用 `Redis` 來作為儲存驅動，這樣性能更好也更符合集羣架構下的使用。

# 安裝

```bash
composer require hyperf/session
```

# 配置

Session 組件的配置儲存於 `config/autoload/session.php` 文件中，如文件不存在，您可通過 `php bin/hyperf.php vendor:publish hyperf/session` 命令來將 Session 組件的配置文件發佈到 Skeleton 去。

## 配置 Session 中間件

在使用 Session 之前，您需要將 `Hyperf\Session\Middleware\SessionMiddleware` 中間件配置為 HTTP Server 的全局中間件，這樣組件才能介入到請求流程進行對應的處理，`config/autoload/middlewares.php` 配置文件示例如下：

```php
<?php

return [
    // 這裏的 http 對應默認的 server name，如您需要在其它 server 上使用 Session，需要對應的配置全局中間件
    'http' => [
        \Hyperf\Session\Middleware\SessionMiddleware::class,
    ],
];
```

## 配置儲存驅動

通過更改配置文件中的 `handler` 配置修改不同的 Session 儲存驅動，而對應 Handler 的具體配置項則由 `options` 內不同的配置項決定。

### 使用文件儲存驅動

> 文件儲存驅動是默認的儲存驅動，但建議生產環境下使用 Redis 驅動

當 `handler` 的值為 `Hyperf\Session\Handler\FileHandler` 時則表明使用 `文件` 儲存驅動，所有的 Session 數據文件都會被生成並儲存在 `options.path` 配置值對應的文件夾中，默認配置的文件夾為根目錄下的 `runtime/session` 文件夾內。

### 使用 Redis 驅動

在使用 `Redis` 儲存驅動之前，您需要安裝 [hyperf/redis](https://github.com/hyperf/redis) 組件。當 `handler` 的值為 `Hyperf\Session\Handler\RedisHandler` 時則表明使用 `Redis` 儲存驅動。您可以通過配置 `options.connection` 配置值來調整驅動要使用的 `Redis` 連接，這裏的連接與 [hyperf/redis](https://github.com/hyperf/redis) 組件的 `config/autoload/redis.php` 配置內的 key 命名匹配，

# 使用

## 獲得 Session 對象

獲得 Session 對象可通過注入 `Hyperf\Contract\SessionInterface`，即可調用接口定義的方法來實現使用：

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

## 儲存數據

當您希望儲存數據到 Session 中去，您可通過調用 `set(string $name, $value): void` 方法來實現：

```php
<?php

$this->session->set('foo', 'bar');
```

## 獲取數據

當您希望從 Session 中獲取數據，您可通過調用 `get(string $name, $default = null)` 方法來實現：

```php
<?php

$this->session->get('foo', $default = null);
```

### 獲取所有數據

您可通過調用 `all(): array` 方法一次性從 Session 中獲得所有的已儲存數據：

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

## 獲取並刪除一條數據

通過調用 `remove(string $name)` 方法可以只使用一個方法就從 Session 中獲取並刪除一條數據：

```php
<?php

$data = $this->session->remove('foo');
```

## 刪除一條或多條數據

通過調用 `forget(string|array $name): void` 方法可以只使用一個方法就從 Session 中刪除一條或多條數據，當傳遞字符串時，表示僅刪除一條數據，當傳遞一個 key 字符串數組時，表示刪除多條數據：

```php
<?php

$this->session->forget('foo');
$this->session->forget(['foo', 'bar']);
```

## 清空當前 Session 數據

當您希望清空當前 Session 裏的所有數據，您可通過調用 `clear(): void` 方法來實現：

```php
<?php

$this->session->clear();
```

## 獲取當前的 Session ID

當您希望獲取當前帶 Session ID 去自行處理一些邏輯時，您可通過調用 `getId(): string` 方法來獲取當前的 Session ID：

```php
<?php

$sessionId = $this->session->getId();
```

