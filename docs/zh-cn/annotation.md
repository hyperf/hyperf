# 注解

注解是 Hyperf 非常强大的一项功能，可以通过注解的形式减少很多的配置，以及实现很多非常方便的功能。

## 概念

### 什么是注解？

注解功能提供了代码中的声明部分都可以添加结构化、机器可读的元数据的能力， 注解的目标可以是类、方法、函数、参数、属性、类常量。 通过 反射 API 可在运行时获取注解所定义的元数据。 因此注解可以成为直接嵌入代码的配置式语言。

通过注解的使用，在应用中实现功能、使用功能可以相互解耦。 某种程度上讲，它可以和接口（interface）与其实现（implementation）相比较。 但接口与实现是代码相关的，注解则与声明额外信息和配置相关。 接口可以通过类来实现，而注解也可以声明到方法、函数、参数、属性、类常量中。 因此它们比接口更灵活。

注解使用的一个简单例子：将接口（interface）的可选方法改用注解实现。 我们假设接口 ActionHandler 代表了应用的一个操作： 部分 action handler 的实现需要 setup，部分不需要。 我们可以使用注解，而不用要求所有类必须实现 ActionHandler 接口并实现 setUp() 方法。 因此带来一个好处——可以多次使用注解。

### 注解是如何发挥作用的？

我们有说到注解只是元数据的定义，需配合应用程序才能发挥作用，在 Hyperf 里，注解内的数据会被收集到 `Hyperf\Di\Annotation\AnnotationCollector` 类供应用程序使用，当然根据您的实际情况，也可以收集到您自定义的类去，随后在这些注解本身希望发挥作用的地方对已收集的注解元数据进行读取和利用，以达到期望的功能实现。

### 忽略某些注解

在一些情况下我们可能希望忽略某些 注解，比如我们在接入一些自动生成文档的工具时，有不少工具都是通过注解的形式去定义文档的相关结构内容的，而这些注解可能并不符合 Hyperf 的使用方式，我们可以通过在 `config/autoload/annotations.php` 内将相关注解设置为忽略。

```php
use JetBrains\PhpStorm\ArrayShape;

return [
    'scan' => [
        // ignore_annotations 数组内的注解都会被注解扫描器忽略
        'ignore_annotations' => [
            ArrayShape::class,
        ],
    ],
];
```

## 使用注解

注解一共有 3 种应用对象，分别是 `类`、`类方法` 和 `类属性`。

### 使用类注解

类注解定义是在 `class` 关键词上方的注释块内，比如常用的 `Controller` 和 `AutoController` 就是类注解的使用典范，下面的代码示例则为一个正确使用类注解的示例，表明 `ClassAnnotation` 注解应用于 `Foo` 类。

```php
<?php
#[ClassAnnotation]
class Foo {}
```

### 使用类方法注解

类方法注解定义是在方法上方的注释块内，比如常用的 `RequestMapping` 就是类方法注解的使用典范，下面的代码示例则为一个正确使用类方法注解的示例，表明 `MethodAnnotation` 注解应用于 `Foo::bar()` 方法。

```php
<?php
class Foo
{
    #[MethodAnnotation]
    public function bar()
    {
        // some code
    }
}
```

### 使用类属性注解

类属性注解定义是在属性上方的注释块内，比如常用的 `Value` 和 `Inject` 就是类属性注解的使用典范，下面的代码示例则为一个正确使用类属性注解的示例，表明 `PropertyAnnotation` 注解应用于 `Foo` 类的 `$bar` 属性。

```php
<?php
class Foo
{
    #[PropertyAnnotation]
    private $bar;
}
```

### 注释型注解参数传递

- 传递主要的单个参数 `#[DemoAnnotation('value')]`
- 传递字符串参数 `#[DemoAnnotation(key1: 'value1', key2: 'value2')]`
- 传递数组参数 `#[DemoAnnotation(key: ['value1', 'value2'])]`

## 自定义注解

### 创建一个注解类

```php
<?php
namespace App\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Foo extends AbstractAnnotation
{
    public function __construct(public array $bar, public int $baz = 0)
    {
    }
}
```

使用注解类：

```php
<?php
use App\Annotation\Foo;

#[Foo(bar: [1, 2], baz: 3)]
class IndexController extends AbstractController
{
    // 利用注解数据
}
```

我们注意一下在上面的示例代码中，注解类都继承了 `Hyperf\Di\Annotation\AbstractAnnotation` 抽象类，对于注解类来说，这个不是必须的，但对于 Hyperf 的注解类来说，继承 `Hyperf\Di\Annotation\AnnotationInterface` 接口类是必须的，那么抽象类在这里的作用是提供极简的定义方式，该抽象类已经为您实现了`注解参数自动分配到类属性`、`根据注解使用位置自动按照规则收集到 AnnotationCollector` 这样非常便捷的功能。

### 自定义注解收集器

收集注解的具体执行流程也是在注解类内实现，相关的方法由 `Hyperf\Di\Annotation\AnnotationInterface` 约束着，该接口类要求了下面 3 个方法的实现，您可以根据自己的需求实现对应的逻辑：

- `public function collectClass(string $className): void;` 当注解定义在类时被扫描时会触发该方法
- `public function collectMethod(string $className, ?string $target): void;` 当注解定义在类方法时被扫描时会触发该方法
- `public function collectProperty(string $className, ?string $target): void` 当注解定义在类属性时被扫描时会触发该方法

因为框架实现了注解收集器缓存功能，所以需要您将自定义收集器配置到 `annotations.scan.collectors` 中，这样框架才能自动缓存收集好的注解，在下次启动时进行复用。
如果没有配置对应的收集器，就会导致自定义注解只有在首次启动 `server` 时生效，而再次启动时不会生效。

```php
<?php

return [
    // 注意在 config/autoload 文件下的配置文件则无 annotations 这一层
    'annotations' => [
        'scan' => [
            'collectors' => [
                CustomCollector::class,
            ],
        ],
    ],
];

```

### 利用注解数据

在没有自定义注解收集方法时，默认会将注解的元数据统一收集在 `Hyperf\Di\Annotation\AnnotationCollector` 类内，通过该类的静态方法可以方便的获取对应的元数据用于逻辑判断或实现。

### ClassMap 功能

框架提供了 `class_map` 配置，可以方便用户直接替换需要加载的类。

比如以下我们实现一个可以自动复制协程上下文的功能：

首先，我们实现一个用于复制上下文的 `Coroutine` 类。其中 `create()` 方法，可以将父类的上下文复制到子类当中。

为了避免命名冲突，约定使用 `class_map` 做为文件夹名，后跟要替换的命名空间的文件夹及文件。

如： `class_map/Hyperf/Coroutine/Coroutine.php`

[Coroutine.php](https://github.com/hyperf/biz-skeleton/blob/master/app/Kernel/Context/Coroutine.php)

```php
<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Kernel\Context;

use App\Kernel\Log\AppendRequestIdProcessor;
use Hyperf\Context\Context;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Engine\Coroutine as Co;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class Coroutine
{
    protected LoggerInterface $logger;

    public function __construct(protected ContainerInterface $container)
    {
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    /**
     * @return int Returns the coroutine ID of the coroutine just created.
     *             Returns -1 when coroutine create failed.
     */
    public function create(callable $callable): int
    {
        $id = Co::id();
        $coroutine = Co::create(function () use ($callable, $id) {
            try {
                // Shouldn't copy all contexts to avoid socket already been bound to another coroutine.
                Context::copy($id, [
                    AppendRequestIdProcessor::REQUEST_ID,
                    ServerRequestInterface::class,
                ]);
                $callable();
            } catch (Throwable $throwable) {
                $this->logger->warning((string) $throwable);
            }
        });

        try {
            return $coroutine->getId();
        } catch (Throwable $throwable) {
            $this->logger->warning((string) $throwable);
            return -1;
        }
    }
}

```

然后，我们实现一个跟 `Hyperf\Coroutine\Coroutine` 一模一样的对象。其中 `create()` 方法替换成我们上述实现的方法。

[Coroutine.php](https://github.com/hyperf/biz-skeleton/blob/master/app/Kernel/ClassMap/Coroutine.php)

`class_map/Hyperf/Coroutine/Coroutine.php`

```php
<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Coroutine;

use App\Kernel\Context\Coroutine as Go;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Engine\Coroutine as Co;
use Hyperf\Engine\Exception\CoroutineDestroyedException;
use Hyperf\Engine\Exception\RunningInNonCoroutineException;
use Throwable;

class Coroutine
{
    /**
     * Returns the current coroutine ID.
     * Returns -1 when running in non-coroutine context.
     */
    public static function id(): int
    {
        return Co::id();
    }

    public static function defer(callable $callable): void
    {
        Co::defer(static function () use ($callable) {
            try {
                $callable();
            } catch (Throwable $exception) {
                di()->get(StdoutLoggerInterface::class)->error((string) $exception);
            }
        });
    }

    public static function sleep(float $seconds): void
    {
        usleep(intval($seconds * 1000 * 1000));
    }

    /**
     * Returns the parent coroutine ID.
     * Returns 0 when running in the top level coroutine.
     * @throws RunningInNonCoroutineException when running in non-coroutine context
     * @throws CoroutineDestroyedException when the coroutine has been destroyed
     */
    public static function parentId(?int $coroutineId = null): int
    {
        return Co::pid($coroutineId);
    }

    /**
     * @return int Returns the coroutine ID of the coroutine just created.
     *             Returns -1 when coroutine create failed.
     */
    public static function create(callable $callable): int
    {
        return di()->get(Go::class)->create($callable);
    }

    public static function inCoroutine(): bool
    {
        return Co::id() > 0;
    }

    public static function stats(): array
    {
        return Co::stats();
    }

    public static function exists(int $id): bool
    {
        return Co::exists($id);
    }
}

```

然后配置一下 `class_map`，如下：

```php
<?php

declare(strict_types=1);

use Hyperf\Coroutine\Coroutine;

return [
    'scan' => [
        'paths' => [
            BASE_PATH . '/app',
        ],
        'ignore_annotations' => [
            'mixin',
        ],
        'class_map' => [
            // 需要映射的类名 => 类所在的文件地址
            Coroutine::class => BASE_PATH . '/class_map/Hyperf/Coroutine/Coroutine.php',
        ],
    ],
];

```

这样 `co()` 和 `parallel()` 等方法，就可以自动拿到父协程，上下文中的数据，比如 `Request`。
