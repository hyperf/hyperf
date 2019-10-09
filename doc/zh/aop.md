# AOP 面向切面编程

## 概念

AOP 为 `Aspect Oriented Programming` 的缩写，意为：`面向切面编程`，通过动态代理等技术实现程序功能的统一维护的一种技术。AOP 是 OOP 的延续，也是 Hyperf 中的一个重要内容，是函数式编程的一种衍生范型。利用 AOP 可以对业务逻辑的各个部分进行隔离，从而使得业务逻辑各部分之间的耦合度降低，提高程序的可重用性，同时提高了开发的效率。   

用通俗的话来讲，就是在 Hyperf 里可以通过 `切面(Aspect)` 介入到由 [hyperf/di](https://github.com/hyperf-cloud/di) 管理的任意类的任意方法的执行流程中去，从而改变或加强原方法的功能，这就是 AOP。

> 使用 AOP 功能必须使用 [hyperf/di](https://github.com/hyperf-cloud/di) 来作为依赖注入容器

## 介绍

相对于其它框架实现的 AOP 功能的使用方式，我们进一步简化了该功能的使用不做过细的划分，仅存在 `环绕(Around)` 一种通用的形式：

- `切面(Aspect)` 为对流程织入的定义类，包括要介入的目标，以及实现对原方法的修改加强处理
- `代理类(ProxyClass)` ，每个被介入的目标类最终都会生成一个代理类，来达到执行 `切面(Aspect)` 方法的目的，而非通过原类

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

/**
 * @Aspect
 */
class FooAspect extends AbstractAspect
{
    // 要切入的类，可以多个，亦可通过 :: 标识到具体的某个方法，通过 * 可以模糊匹配
    public $classes = [
        SomeClass::class,
        'App\Service\SomeClass::someMethod',
        'App\Service\SomeClass::*Method',
    ];
    
    // 要切入的注解，具体切入的还是使用了这些注解的类，仅可切入类注解和类方法注解
    public $annotations = [
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

每个 `切面(Aspect)` 必须定义 `@Aspect` 注解或在 `config/autoload/aspects.php` 内配置均可发挥作用。

> 使用 `@Aspect` 注解时需 `use Hyperf\Di\Annotation\Aspect;` 命名空间；  

## 代理类缓存

所有被 AOP 影响的类，都会在 `./runtime/container/proxy/` 文件夹内生成对应的 `代理类缓存`，服务启动时，如果类所对应的代理类缓存存在，则不会重新生成直接使用缓存，即使 `Aspect` 的切入范围发生了改变。不存在时，则会自动重新生成新的代理类缓存。   

在部署生产环境时，我们可能会希望 Hyperf 提前将所有代理类提前生成，而不是使用时动态的生成，可以通过 `php bin/hyperf.php di:init-proxy` 命令来生成所有代理类，该命令会忽视现有的代理类缓存，全部重新生成。   

基于以上，我们可以将生成代理类的命令和启动服务的命令结合起来，`php bin/hyperf.php di:init-proxy && php bin/hyperf.php start` 来达到自动重新生成所有代理类缓存然后启动服务的目的。
