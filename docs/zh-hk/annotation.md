# 註解

註解是 Hyperf 非常強大的一項功能，可以通過註解的形式減少很多的配置，以及實現很多非常方便的功能。

## 概念

### 什麼是註解？

註解功能提供了代碼中的聲明部分都可以添加結構化、機器可讀的元數據的能力， 註解的目標可以是類、方法、函數、參數、屬性、類常量。 通過 反射 API 可在運行時獲取註解所定義的元數據。 因此註解可以成為直接嵌入代碼的配置式語言。

通過註解的使用，在應用中實現功能、使用功能可以相互解耦。 某種程度上講，它可以和接口（interface）與其實現（implementation）相比較。 但接口與實現是代碼相關的，註解則與聲明額外信息和配置相關。 接口可以通過類來實現，而註解也可以聲明到方法、函數、參數、屬性、類常量中。 因此它們比接口更靈活。

註解使用的一個簡單例子：將接口（interface）的可選方法改用註解實現。 我們假設接口 ActionHandler 代表了應用的一個操作： 部分 action handler 的實現需要 setup，部分不需要。 我們可以使用註解，而不用要求所有類必須實現 ActionHandler 接口並實現 setUp() 方法。 因此帶來一個好處——可以多次使用註解。

### 註解是如何發揮作用的？

我們有説到註解只是元數據的定義，需配合應用程序才能發揮作用，在 Hyperf 裏，註解內的數據會被收集到 `Hyperf\Di\Annotation\AnnotationCollector` 類供應用程序使用，當然根據您的實際情況，也可以收集到您自定義的類去，隨後在這些註解本身希望發揮作用的地方對已收集的註解元數據進行讀取和利用，以達到期望的功能實現。

### 忽略某些註解

在一些情況下我們可能希望忽略某些 註解，比如我們在接入一些自動生成文檔的工具時，有不少工具都是通過註解的形式去定義文檔的相關結構內容的，而這些註解可能並不符合 Hyperf 的使用方式，我們可以通過在 `config/autoload/annotations.php` 內將相關注解設置為忽略。

```php
use JetBrains\PhpStorm\ArrayShape;

return [
    'scan' => [
        // ignore_annotations 數組內的註解都會被註解掃描器忽略
        'ignore_annotations' => [
            ArrayShape::class,
        ],
    ],
];
```

## 使用註解

註解一共有 3 種應用對象，分別是 `類`、`類方法` 和 `類屬性`。

### 使用類註解

類註解定義是在 `class` 關鍵詞上方的註釋塊內，比如常用的 `Controller` 和 `AutoController` 就是類註解的使用典範，下面的代碼示例則為一個正確使用類註解的示例，表明 `ClassAnnotation` 註解應用於 `Foo` 類。

```php
<?php
#[ClassAnnotation]
class Foo {}
```

### 使用類方法註解

類方法註解定義是在方法上方的註釋塊內，比如常用的 `RequestMapping` 就是類方法註解的使用典範，下面的代碼示例則為一個正確使用類方法註解的示例，表明 `MethodAnnotation` 註解應用於 `Foo::bar()` 方法。

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

### 使用類屬性註解

類屬性註解定義是在屬性上方的註釋塊內，比如常用的 `Value` 和 `Inject` 就是類屬性註解的使用典範，下面的代碼示例則為一個正確使用類屬性註解的示例，表明 `PropertyAnnotation` 註解應用於 `Foo` 類的 `$bar` 屬性。

```php
<?php
class Foo
{
    #[PropertyAnnotation]
    private $bar;
}
```

### 註釋型註解參數傳遞

- 傳遞主要的單個參數 `#[DemoAnnotation('value')]`
- 傳遞字符串參數 `#[DemoAnnotation(key1: 'value1', key2: 'value2')]`
- 傳遞數組參數 `#[DemoAnnotation(key: ['value1', 'value2'])]`

## 自定義註解

### 創建一個註解類

在任意地方創建註解類，如下代碼示例：

```php
<?php
namespace App\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Bar extends AbstractAnnotation
{
    // some code
}

#[Attribute(Attribute::TARGET_CLASS)]
class Foo extends AbstractAnnotation
{
    // some code
}
```

我們注意一下在上面的示例代碼中，註解類都繼承了 `Hyperf\Di\Annotation\AbstractAnnotation` 抽象類，對於註解類來説，這個不是必須的，但對於 Hyperf 的註解類來説，繼承 `Hyperf\Di\Annotation\AnnotationInterface` 接口類是必須的，那麼抽象類在這裏的作用是提供極簡的定義方式，該抽象類已經為您實現了`註解參數自動分配到類屬性`、`根據註解使用位置自動按照規則收集到 AnnotationCollector` 這樣非常便捷的功能。

### 自定義註解收集器

註解的收集時具體的執行流程也是在註解類內實現的，相關的方法由 `Hyperf\Di\Annotation\AnnotationInterface` 約束着，該接口類要求了下面 3 個方法的實現，您可以根據自己的需求實現對應的邏輯：

- `public function collectClass(string $className): void;` 當註解定義在類時被掃描時會觸發該方法
- `public function collectMethod(string $className, ?string $target): void;` 當註解定義在類方法時被掃描時會觸發該方法
- `public function collectProperty(string $className, ?string $target): void` 當註解定義在類屬性時被掃描時會觸發該方法

因為框架實現了註解收集器緩存功能，所以需要您將自定義收集器配置到 `annotations.scan.collectors` 中，這樣框架才能自動緩存收集好的註解，在下次啓動時進行復用。
如果沒有配置對應的收集器，就會導致自定義註解只有在首次啓動 `server` 時生效，而再次啓動時不會生效。

```php
<?php

return [
    // 注意在 config/autoload 文件下的配置文件則無 annotations 這一層
    'annotations' => [
        'scan' => [
            'collectors' => [
                CustomCollector::class,
            ],
        ],
    ],
];

```

### 利用註解數據

在沒有自定義註解收集方法時，默認會將註解的元數據統一收集在 `Hyperf\Di\Annotation\AnnotationCollector` 類內，通過該類的靜態方法可以方便的獲取對應的元數據用於邏輯判斷或實現。

### ClassMap 功能

框架提供了 `class_map` 配置，可以方便用户直接替換需要加載的類。

比如以下我們實現一個可以自動複製協程上下文的功能：

首先，我們實現一個用於複製上下文的 `Coroutine` 類。其中 `create()` 方法，可以將父類的上下文複製到子類當中。

為了避免命名衝突，約定使用 `class_map` 做為文件夾名，後跟要替換的命名空間的文件夾及文件。

如： `class_map/Hyperf/Utils/Coroutine.php`

```php
<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Kernel\Context;

use Hyperf\Context\Context;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\Utils;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swoole\Coroutine as SwooleCoroutine;

class Coroutine
{
    protected StdoutLoggerInterface $logger;
    
    protected ?FormatterInterface $formatter = null;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->logger = $container->get(StdoutLoggerInterface::class);
        if ($container->has(FormatterInterface::class)) {
            $this->formatter = $container->get(FormatterInterface::class);
        }
    }

    /**
     * @return int Returns the coroutine ID of the coroutine just created.
     *             Returns -1 when coroutine create failed.
     */
    public function create(callable $callable): int
    {
        $id = Utils\Coroutine::id();
        $result = SwooleCoroutine::create(function () use ($callable, $id) {
            try {
                // 按需複製，禁止複製 Socket，不然會導致 Socket 跨協程調用從而報錯。
                Context::copy($id, [
                    ServerRequestInterface::class,
                ]);
                call($callable);
            } catch (Throwable $throwable) {
                if ($this->formatter) {
                    $this->logger->warning($this->formatter->format($throwable));
                } else {
                    $this->logger->warning((string) $throwable);
                }
            }
        });
        return is_int($result) ? $result : -1;
    }
}

```

然後，我們實現一個跟 `Hyperf\Coroutine\Coroutine` 一模一樣的對象。其中 `create()` 方法替換成我們上述實現的方法。

`class_map/Hyperf/Coroutine/Coroutine.php`

```php
<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Coroutine;

use App\Kernel\Context\Coroutine as Co;
use Swoole\Coroutine as SwooleCoroutine;
use Hyperf\Context\ApplicationContext;

/**
 * @method static void defer(callable $callable)
 */
class Coroutine
{
    public static function __callStatic($name, $arguments)
    {
        if (! method_exists(SwooleCoroutine::class, $name)) {
            throw new \BadMethodCallException(sprintf('Call to undefined method %s.', $name));
        }
        return SwooleCoroutine::$name(...$arguments);
    }

    /**
     * Returns the current coroutine ID.
     * Returns -1 when running in non-coroutine context.
     */
    public static function id(): int
    {
        return SwooleCoroutine::getCid();
    }

    /**
     * Returns the parent coroutine ID.
     * Returns -1 when running in the top level coroutine.
     * Returns null when running in non-coroutine context.
     *
     * @see https://github.com/swoole/swoole-src/pull/2669/files#diff-3bdf726b0ac53be7e274b60d59e6ec80R940
     */
    public static function parentId(?int $coroutineId = null): ?int
    {
        if ($coroutineId) {
            $cid = SwooleCoroutine::getPcid($coroutineId);
        } else {
            $cid = SwooleCoroutine::getPcid();
        }
        if ($cid === false) {
            return null;
        }

        return $cid;
    }

    /**
     * @return int Returns the coroutine ID of the coroutine just created.
     *             Returns -1 when coroutine create failed.
     */
    public static function create(callable $callable): int
    {
        return ApplicationContext::getContainer()->get(Co::class)->create($callable);
    }

    public static function inCoroutine(): bool
    {
        return Coroutine::id() > 0;
    }
}

```

然後配置一下 `class_map`，如下：

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
            // 需要映射的類名 => 類所在的文件地址
            Coroutine::class => BASE_PATH . '/class_map/Hyperf/Utils/Coroutine.php',
        ],
    ],
];

```

這樣 `co()` 和 `parallel()` 等方法，就可以自動拿到父協程，上下文中的數據，比如 `Request`。
