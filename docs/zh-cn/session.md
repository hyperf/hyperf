# Session 会话管理

HTTP 是一种无状态协议，即服务器不保留与客户交易时的任何状态，所以当我们在开发 HTTP Server 应用时，我们通常会通过 Session 来实现多个请求之间用户数据的共享。您可通过 [hyperf/session](https://github.com/hyperf/session) 来实现 Session 的功能。Session 组件当前仅适配了两种储存驱动，分别为 `文件` 和 `Redis`，默认为 `文件` 驱动，在生产环境下，我们强烈建议您使用 `Redis` 来作为储存驱动，这样性能更好也更符合集群架构下的使用。

# 安装

```bash
composer require hyperf/session
```

# 配置

Session 组件的配置储存于 `config/autoload/session.php` 文件中，如文件不存在，您可通过 `php bin/hyperf.php vendor:publish hyperf/session` 命令来将 Session 组件的配置文件发布到 Skeleton 去。

## 配置 Session 中间件

在使用 Session 之前，您需要将 `Hyperf\Session\Middleware\SessionMiddleware` 中间件配置为 HTTP Server 的全局中间件，这样组件才能介入到请求流程进行对应的处理，`config/autoload/middlewares.php` 配置文件示例如下：

```php
<?php

return [
    // 这里的 http 对应默认的 server name，如您需要在其它 server 上使用 Session，需要对应的配置全局中间件
    'http' => [
        \Hyperf\Session\Middleware\SessionMiddleware::class,
    ],
];
```

## 配置储存驱动

通过更改配置文件中的 `handler` 配置修改不同的 Session 储存驱动，而对应 Handler 的具体配置项则由 `options` 内不同的配置项决定。

### 使用文件储存驱动

> 文件储存驱动是默认的储存驱动，但建议生产环境下使用 Redis 驱动

当 `handler` 的值为 `Hyperf\Session\Handler\FileHandler` 时则表明使用 `文件` 储存驱动，所有的 Session 数据文件都会被生成并储存在 `options.path` 配置值对应的文件夹中，默认配置的文件夹为根目录下的 `runtime/session` 文件夹内。

### 使用 Redis 驱动

在使用 `Redis` 储存驱动之前，您需要安装 [hyperf/redis](https://github.com/hyperf/redis) 组件。当 `handler` 的值为 `Hyperf\Session\Handler\RedisHandler` 时则表明使用 `Redis` 储存驱动。您可以通过配置 `options.connection` 配置值来调整驱动要使用的 `Redis` 连接，这里的连接与 [hyperf/redis](https://github.com/hyperf/redis) 组件的 `config/autoload/redis.php` 配置内的 key 命名匹配，

# 使用

## 获得 Session 对象

获得 Session 对象可通过注入 `Hyperf\Contract\SessionInterface`，即可调用接口定义的方法来实现使用：

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
        // 直接通过 $this->session 来使用
    } 
}
```

## 储存数据

当您希望储存数据到 Session 中去，您可通过调用 `set(string $name, $value): void` 方法来实现：

```php
<?php

$this->session->set('foo', 'bar');
```

## 获取数据

当您希望从 Session 中获取数据，您可通过调用 `get(string $name, $default = null)` 方法来实现：

```php
<?php

$this->session->get('foo', $default = null);
```

### 获取所有数据

您可通过调用 `all(): array` 方法一次性从 Session 中获得所有的已储存数据：

```php
<?php

$data = $this->session->all();
```

## 判断 Session 中是否存在某个值

要确定 Session 中是否存在某个值，可以使用 `has(string $name): bool` 方法。如果该值存在且不为 null，那么 `has` 方法会返回 `true`：

```php
<?php

if ($this->session->has('foo')) {
    //
}
```

## 获取并删除一条数据

通过调用 `remove(string $name)` 方法可以只使用一个方法就从 Session 中获取并删除一条数据：

```php
<?php

$data = $this->session->remove('foo');
```

## 删除一条或多条数据

通过调用 `forget(string|array $name): void` 方法可以只使用一个方法就从 Session 中删除一条或多条数据，当传递字符串时，表示仅删除一条数据，当传递一个 key 字符串数组时，表示删除多条数据：

```php
<?php

$this->session->forget('foo');
$this->session->forget(['foo', 'bar']);
```

## 清空当前 Session 数据

当您希望清空当前 Session 里的所有数据，您可通过调用 `clear(): void` 方法来实现：

```php
<?php

$this->session->clear();
```

## 获取当前的 Session ID

当您希望获取当前带 Session ID 去自行处理一些逻辑时，您可通过调用 `getId(): string` 方法来获取当前的 Session ID：

```php
<?php

$sessionId = $this->session->getId();
```

