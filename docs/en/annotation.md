# Annotation

Annotation is a very powerful feature of Hyperf, which can reduce a lot of configuration and implement many very convenient functions through annotations.

## Concept

### What is an Annotation?

The annotation function provides the ability to add structured, machine-readable metadata to the declaration parts in the code. The target of an annotation can be a class, method, function, parameter, property, or class constant. Through the Reflection API, the metadata defined by the annotation can be obtained at runtime. Therefore, annotations can become a configuration-like language embedded directly into the code.

Through the use of annotations, implementing functions and using functions in an application can be decoupled from each other. To some extent, it can be compared to an interface and its implementation. But interfaces and implementations are code-related, while annotations are related to declaring additional information and configuration. Interfaces can be implemented through classes, and annotations can also be declared in methods, functions, parameters, properties, and class constants. Therefore, they are more flexible than interfaces.

A simple example of annotation usage: implementing optional methods of an interface using annotations. Suppose the interface `ActionHandler` represents an operation of an application: some `action handler` implementations need `setup`, and some do not. We can use annotations instead of requiring all classes to implement the `ActionHandler` interface and implement the `setUp()` method. This brings a benefit — you can use annotations multiple times.

### How do Annotations Work?

We have mentioned that annotations are just metadata definitions and need to be used in conjunction with the application to work. In Hyperf, the data in annotations will be collected into the `Hyperf\Di\Annotation\AnnotationCollector` class for the application to use. Of course, according to your actual situation, it can also be collected into your custom class. Subsequently, the collected annotation metadata is read and utilized where these annotations themselves are intended to work, to achieve the desired functional implementation.

### Ignoring Certain Annotations

In some cases, we may want to ignore certain annotations. For example, when we access some tools that automatically generate documentation, many tools define the relevant structure content of the documentation through annotations, and these annotations may not conform to the usage method of Hyperf. We can set the relevant annotations to be ignored in `config/autoload/annotations.php`.

```php
use JetBrains\PhpStorm\ArrayShape;

return [
    'scan' => [
        // Annotations in the ignore_annotations array will be ignored by the annotation scanner
        'ignore_annotations' => [
            ArrayShape::class,
        ],
    ],
];
```

## Using Annotations

There are 3 types of application objects for annotations: `class`, `class method`, and `class property`.

### Using Class Annotations

Class annotation definition is in the comment block above the `class` keyword. For example, the commonly used `Controller` and `AutoController` are classic examples of class annotation usage. The code example below is an example of correctly using class annotations, indicating that the `ClassAnnotation` annotation is applied to the `Foo` class.

```php
<?php
#[ClassAnnotation]
class Foo {}
```

### Using Class Method Annotations

Class method annotation definition is in the comment block above the method. For example, the commonly used `RequestMapping` is a classic example of class method annotation usage. The code example below is an example of correctly using class method annotations, indicating that the `MethodAnnotation` annotation is applied to the `Foo::bar()` method.

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

### Using Class Property Annotations

Class property annotation definition is in the comment block above the property. For example, the commonly used `Value` and `Inject` are classic examples of class property annotation usage. The code example below is an example of correctly using class property annotations, indicating that the `PropertyAnnotation` annotation is applied to the `$bar` property of the `Foo` class.

```php
<?php
class Foo
{
    #[PropertyAnnotation]
    private $bar;
}
```

### Comment-type Annotation Parameter Passing

- Passing the main single parameter `#[DemoAnnotation('value')]`
- Passing string parameters `#[DemoAnnotation(key1: 'value1', key2: 'value2')]`
- Passing array parameters `#[DemoAnnotation(key: ['value1', 'value2'])]`

## Custom Annotations

### Creating an Annotation Class

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

Using the annotation class:

```php
<?php
use App\Annotation\Foo;

#[Foo(bar: [1, 2], baz: 3)]
class IndexController extends AbstractController
{
    // Use annotation data
}
```

Note that in the example code above, the annotation classes inherit the `Hyperf\Di\Annotation\AbstractAnnotation` abstract class. For annotation classes, this is not required, but for Hyperf annotation classes, inheriting the `Hyperf\Di\Annotation\AnnotationInterface` interface class is required. The abstract class's role here is to provide a minimalist definition method. This abstract class has already implemented convenient functions for you, such as `automatic distribution of annotation parameters to class properties` and `automatic collection to AnnotationCollector according to rules based on the position of annotation usage`.

### Custom Annotation Collector

The specific execution flow of collecting annotations is also implemented within the annotation class. The related methods are constrained by `Hyperf\Di\Annotation\AnnotationInterface`. This interface class requires the implementation of the following 3 methods, and you can implement the corresponding logic according to your own needs:

- `public function collectClass(string $className): void;` This method is triggered when the annotation defined on the class is scanned
- `public function collectMethod(string $className, ?string $target): void;` This method is triggered when the annotation defined on the class method is scanned
- `public function collectProperty(string $className, ?string $target): void` This method is triggered when the annotation defined on the class property is scanned

Because the framework has implemented the annotation collector cache function, you need to configure the custom collector in `annotations.scan.collectors` so that the framework can automatically cache the collected annotations and reuse them the next time it starts.
If the corresponding collector is not configured, it will cause the custom annotation to only take effect when the `server` is started for the first time, but not when it is started again.

```php
<?php

return [
    // Note that there is no annotations level in the configuration file under the config/autoload folder
    'annotations' => [
        'scan' => [
            'collectors' => [
                CustomCollector::class,
            ],
        ],
    ],
];
```

### Utilizing Annotation Data

When there is no custom annotation collection method, the annotation metadata will be uniformly collected in the `Hyperf\Di\Annotation\AnnotationCollector` class by default. Through the static methods of this class, you can easily obtain the corresponding metadata for logical judgment or implementation.

### ClassMap Feature

The framework provides `class_map` configuration, which can facilitate users to directly replace the classes that need to be loaded.

For example, we implement a function that can automatically copy coroutine context:

First, we implement a `Coroutine` class for copying context. The `create()` method can copy the context of the parent class to the subclass.

To avoid naming conflicts, it is agreed to use `class_map` as the folder name, followed by the folder and file of the namespace to be replaced.

Such as: `class_map/Hyperf/Coroutine/Coroutine.php`

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

Then, we implement an object exactly the same as `Hyperf\Coroutine\Coroutine`. The `create()` method is replaced with the method we implemented above.

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

Then configure `class_map`, as follows:

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
            // Class name to be mapped => File path where the class is located
            Coroutine::class => BASE_PATH . '/class_map/Hyperf/Coroutine/Coroutine.php',
        ],
    ],
];
```

In this way, methods such as `co()` and `parallel()` can automatically get the parent coroutine, and the data in the context, such as `Request`.
