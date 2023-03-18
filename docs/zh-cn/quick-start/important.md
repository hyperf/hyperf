# 编程须知

这里收集各种通过 Hyperf 编程前应该知晓的知识点或内容点。

## 不能通过全局变量获取属性参数

在 `PHP-FPM` 下可以通过全局变量获取到请求的参数，服务器的参数等，在 `Hyperf` 和 `Swoole` 内，都 **无法** 通过 `$_GET/$_POST/$_REQUEST/$_SESSION/$_COOKIE/$_SERVER`等`$_`开头的变量获取到任何属性参数。

## 通过容器获取的类都是单例

通过依赖注入容器获取的都是进程内持久化的，是多个协程共享的，所以不能包含任何的请求唯一的数据或协程唯一的数据，这类型的数据都通过协程上下文去处理，具体请仔细阅读 [依赖注入](zh-cn/di.md) 和 [协程](zh-cn/coroutine.md) 章节。

## 项目部署

> 官方的 Dockerfile 已经完成了以下操作。

线上代码部署时，请务必开启 `scan_cacheable`。

开启此配置后，首次扫描时会生成代理类和注解缓存，再次启动时，则可以直接使用缓存，极大优化内存使用率和启动速度。因为跳过了扫描阶段，所以会依赖 `Composer Class Map`，故我们必须要执行 `--optimize-autoloader` 优化索引。

综上，线上更新代码，重启项目前，需要执行以下命令

```bash
# 优化 Composer 索引
composer dump-autoload -o
# 生成代理类和注解缓存
php bin/hyperf.php
```


## 避免在魔术方法中切换协程

> __call __callStatic 除外

尽量避免在 `__get` `__set` 和 `__isset` 中切换协程，因为可能会出现不符合预期的情况

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

当我们执行上述代码时，会返回以下结果

```shell
bool(false)
string(3) "xxx"
bool(true)
```
