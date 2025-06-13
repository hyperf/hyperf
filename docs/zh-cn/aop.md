# AOP 面向切面编程

## 概念

AOP 为 `Aspect Oriented Programming` 的缩写，意为：`面向切面编程`，通过动态代理等技术实现程序功能的统一维护的一种技术。AOP 是 OOP 的延续，也是 Hyperf 中的一个重要内容，是函数式编程的一种衍生范型。利用 AOP 可以对业务逻辑的各个部分进行隔离，从而使得业务逻辑各部分之间的耦合度降低，提高程序的可重用性，同时提高了开发的效率。

用通俗的话来讲，就是在 Hyperf 里可以通过 `切面(Aspect)` 介入到任意类的任意方法的执行流程中去，从而改变或加强原方法的功能，这就是 AOP。

> 注意这里所指的任意类并不是完全意义上的所有类，在 Hyperf 启动初期用于实现 AOP 功能的类自身不能被切入。

## 介绍

相对于其它框架实现的 AOP 功能的使用方式，我们进一步简化了该功能的使用不做过细的划分，仅存在 `环绕(Around)` 一种通用的形式：

- `切面(Aspect)` 为对流程织入的定义类，包括要介入的目标，以及实现对原方法的修改加强处理
- `代理类(ProxyClass)` ，每个被介入的目标类最终都会生成一个代理类，来达到执行 `切面(Aspect)` 方法的目的

## 定义切面(Aspect)

每个 `切面(Aspect)` 必须实现 `Hyperf\Di\Aop\AroundInterface` 接口，并提供 `public` 的 `$classes` 和 `$annotations` 属性，为了方便使用，我们可以通过继承 `Hyperf\Di\Aop\AbstractAspect` 来简化定义过程，我们通过代码来描述一下。

```php
<?php
namespace App\Aspect;

use App\Service\SomeClass;
use App\Annotation\SomeAnnotation;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

#[Aspect]
class FooAspect extends AbstractAspect
{
    // 要切入的类或 Trait，可以多个，亦可通过 :: 标识到具体的某个方法，通过 * 可以模糊匹配
    public array $classes = [
        SomeClass::class,
        'App\Service\SomeClass::someMethod',
        'App\Service\SomeClass::*Method',
    ];

    // 要切入的注解，具体切入的还是使用了这些注解的类，仅可切入类注解和类方法注解
    public array $annotations = [
        SomeAnnotation::class,
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        // 切面切入后，执行对应的方法会由此来负责
        // $proceedingJoinPoint 为连接点，通过该类的 process() 方法调用原方法并获得结果
        // 在调用前进行某些处理
        $result = $proceedingJoinPoint->process();
        // 在调用后进行某些处理
        return $result;
    }
}
```

每个 `切面(Aspect)` 必须定义 `#[Aspect]` 注解或在 `config/autoload/aspects.php` 内配置均可发挥作用。

> 使用 `#[Aspect]` 注解时需 `use Hyperf\Di\Annotation\Aspect;` 命名空间；

您也可以通过 `#[Aspect]` 注解本身的属性来完成切入目标的配置，通过下面注解的形式可以达到与上面的示例一样的目的：

```php
<?php
namespace App\Aspect;

use App\Service\SomeClass;
use App\Annotation\SomeAnnotation;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

#[
    Aspect(
        classes: [
            SomeClass::class,
            "App\Service\SomeClass::someMethod",
            "App\Service\SomeClass::*Method"
        ],
        annotations: [
            SomeAnnotation::class
        ]
    )
]
class FooAspect extends AbstractAspect
{
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        // 切面切入后，执行对应的方法会由此来负责
        // $proceedingJoinPoint 为连接点，通过该类的 process() 方法调用原方法并获得结果
        // 在调用前进行某些处理
        $result = $proceedingJoinPoint->process();
        // 在调用后进行某些处理
        return $result;
    }
}
```

## 改变或加强原方法

另外您还可以通过获取原实例、方法反射、提交参数、获取注解等方式实现业务需求：

```php
<?php
namespace App\Aspect;

use App\Service\SomeClass;
use App\Annotation\SomeAnnotation;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

#[Aspect]
class FooAspect extends AbstractAspect
{
    public array $classes = [
        SomeClass::class,
        'App\Service\SomeClass::someMethod',
        'App\Service\SomeClass::*Method',
    ];

    public array $annotations = [
        SomeAnnotation::class,
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        // 获取当前方法反射原型
        /** @var \ReflectionMethod **/
        $reflect = $proceedingJoinPoint->getReflectMethod();

        // 获取调用方法时提交的参数
        $arguments = $proceedingJoinPoint->getArguments(); // array

        // 获取原类的实例并调用原类的其他方法
        $originalInstance = $proceedingJoinPoint->getInstance();
        $originalInstance->yourFunction();

        // 获取注解元数据
        /** @var \Hyperf\Di\Aop\AnnotationMetadata **/
        $metadata = $proceedingJoinPoint->getAnnotationMetadata();

        // 调用不受代理类影响的原方法
        $proceedingJoinPoint->processOriginalMethod();

        // 不执行原方法，做其他操作
        $result = date('YmdHis', time() - 86400);
        return $result;
    }
}
```

> 注意：`getInstance`获取到的类为代理类，里面的方法仍会被其他切面影响，相互嵌套调用会死循环耗尽内存。

## 代理类缓存

所有被 AOP 影响的类，都会在 `./runtime/container/proxy/` 文件夹内生成对应的 `代理类缓存`，是否在启动时自动生成取决于 `config/config.php` 配置文件中 `scan_cacheable` 配置项的值，默认值为 `false`，如果该配置项为 `true` 则 Hyperf 不会扫描和生成代理类缓存，而是直接以现有的缓存文件作为最终的代理类。如果该配置项为 `false`，则 Hyperf 会在每次启动应用时扫描注解扫描域并自动的生成对应的代理类缓存，当代码发生变化时，代理类缓存也会自动的重新生成。

通常在开发环境下，该值为 `false`，这样更便于开发调试，而在部署生产环境时，我们可能会希望 Hyperf 提前将所有代理类提前生成，而不是使用时动态的生成，可以通过 `php bin/hyperf.php` 命令来生成所有代理类，然后再通过环境变量 `SCAN_CACHEABLE` 为 `true` 修改该配置项的值，以达到启动时间更短、应用内存占用更低的目的。

基于以上，如果您使用 Docker 或 Kubernetes 等虚拟化技术来部署您的应用的话，您可以在镜像构建阶段就生成对应的代理类缓存并写入到镜像中去，在运行镜像实例时，可大大减少启动时间和应用内存。
