# 自动惰性加载器

Hyperf的长生命周期依赖注入在项目启动时完成。这意味着长生命周期的类需要注意：

* 构造函数时还不是协程环境，如果注入了可能会触发协程切换的类，就会导致框架启动失败。

* 构造函数中要避免循坏依赖（比较典型的例子为 `Listener` 和 `EventDispatcherInterface`），不然也会启动失败。

目前解决方案是：只在实例中注入 `ContainerInterface` ，而其他的组件在非构造函数执行时通过 `container` 获取。PSR-11中指出:

> 「用户不应该将容器作为参数传入对象然后在对象中通过容器获得对象的依赖。这样是把容器当作服务定位器来使用，而服务定位器是一种反模式」

也就是说这样的做法虽然有效，但是从设计模式角度来说并不推荐。

另一个方案是使用PHP中常用的惰性代理模式，注入一个代理对象，在使用时再实例化目标对象。本组件依赖Hyperf DI组件设计了基于类型提示（TypeHint）的懒加载注入功能。

## 安装

```bash
composer require hyperf/lazy-loader
```

## 实战

下面这个例子会发生循环依赖：

```php
<?php

namespace App\Example;

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeWorkerStart;
use Psr\EventDispatcher\EventDispatcherInterface;

class ExampleListener implements ListenerInterface
{

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;


    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @return string[] returns the events that you want to listen
     */
    public function listen(): array
    {
        return [
            BeforeWorkerStart::class,
        ];
    }

    public function process(object $event)
    {
        $dispatcher->dispatch(new SomeEvent());
    }
}
```

安装本组件后，修改一个地方，就不会发生死循环：

```php
<?php

namespace App\Example;

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeWorkerStart;
use Lazy\Psr\EventDispatcher\EventDispatcherInterface;

class ExampleListener implements ListenerInterface
{

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;


    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @return string[] returns the events that you want to listen
     */
    public function listen(): array
    {
        return [
            BeforeWorkerStart::class,
        ];
    }

    public function process(object $event)
    {
        $dispatcher->dispatch(new SomeEvent());
    }
}
```

> 请耐心找不同...

我们只替换了 `Psr\EventDispatcher\EventDispatcherInterface` 为 `Lazy\Psr\EventDispatcher\EventDispatcherInterface` ，Hyperf DI会识别这个类型提示（TypeHint），自动生成一个代理类并注入到构建函数里。

本组件是完全基于Hyperf DI的，在任何Hyperf DI能够解析的命名空间前加上 `Lazy\` 都可以实现懒加载。被代理对象总是从容器中获取的，也就是说用户在Dependencies配置项中对依赖关系做的任何修改都会被懒加载器尊重。

## 细节

当该代理对象执行下列操作时，被代理对象才会被真正实例化。

```php

// 方法调用
$proxy->someMethod();

// 读取属性
echo $proxy->someProperty;

// 写入属性
$proxy->someProperty = 'foo';

// 检查属性是否存在
isset($proxy->someProperty);

// 删除属性
unset($proxy->someProperty);

```

当您需要获得被代理对象时，可以主动执行 `getInstance` 方法获取：

```php
$trueObject = $proxy->getInstance();
```

## 开发者注

PRE-ALPHA。本组件 100% cool 但是 20% useful。目前只为展示Hyperf DI的灵活性抛砖引玉，不建议生产使用。

