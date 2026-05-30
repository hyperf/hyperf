# AOP Aspect-Oriented Programming

## Concept

AOP is an abbreviation for `Aspect Oriented Programming`, which means using technologies such as dynamic proxy to achieve unified maintenance of program functions. AOP is a continuation of OOP, also an important part of Hyperf, and a derivative paradigm of functional programming. Using AOP can isolate various parts of business logic, thereby reducing the coupling between various parts of business logic, improving the reusability of the program, and improving development efficiency.

In common terms, in Hyperf, you can intervene in the execution flow of any method of any class through `Aspect` to change or strengthen the function of the original method. This is AOP.

> Note that the "any class" referred to here is not all classes in a complete sense. The classes used to implement AOP functionality at the early stage of Hyperf startup cannot be cut into.

## Introduction

Compared to the usage method of AOP functionality implemented by other frameworks, we further simplify the use of this functionality without making overly fine divisions, and only exist in a general form of `Around`:

- `Aspect` is the definition class for flow weaving, including the target to be intervened, and the implementation of modification and reinforcement processing for the original method.
- `ProxyClass`, for each target class that is intervened, a proxy class will eventually be generated to achieve the purpose of executing the `Aspect` method.

## Defining Aspect

Each `Aspect` must implement the `Hyperf\Di\Aop\AroundInterface` interface and provide `public` `$classes` and `$annotations` properties. For ease of use, we can simplify the definition process by inheriting `Hyperf\Di\Aop\AbstractAspect`. We describe it through code.

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
    // The class or Trait to cut into, can be multiple, or can be identified to a specific method through ::, and can be fuzzy matched through *
    public array $classes = [
        SomeClass::class,
        'App\Service\SomeClass::someMethod',
        'App\Service\SomeClass::*Method',
    ];

    // The annotations to cut into. What is actually cut into are the classes that use these annotations. Only class annotations and class method annotations can be cut into
    public array $annotations = [
        SomeAnnotation::class,
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        // After the aspect is cut into, the execution of the corresponding method will be responsible by this
        // $proceedingJoinPoint is the connection point, call the original method through the process() method of this class and obtain the result
        // Perform some processing before calling
        $result = $proceedingJoinPoint->process();
        // Perform some processing after calling
        return $result;
    }
}
```

Each `Aspect` must define the `#[Aspect]` annotation or be configured in `config/autoload/aspects.php` to take effect.

> When using the `#[Aspect]` annotation, you need to `use Hyperf\Di\Annotation\Aspect;` namespace;

You can also complete the configuration of the target to be cut into through the attributes of the `#[Aspect]` annotation itself. You can achieve the same purpose as the example above through the form of the following annotation:

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
        // After the aspect is cut into, the execution of the corresponding method will be responsible by this
        // $proceedingJoinPoint is the connection point, call the original method through the process() method of this class and obtain the result
        // Perform some processing before calling
        $result = $proceedingJoinPoint->process();
        // Perform some processing after calling
        return $result;
    }
}
```

## Changing or Strengthening Original Methods

In addition, you can also implement business requirements by obtaining the original instance, method reflection, submitting parameters, obtaining annotations, etc.:

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
        // Obtain current method reflection prototype
        /** @var \ReflectionMethod **/
        $reflect = $proceedingJoinPoint->getReflectMethod();

        // Obtain parameters submitted when calling the method
        $arguments = $proceedingJoinPoint->getArguments(); // array

        // Obtain an instance of the original class and call other methods of the original class
        $originalInstance = $proceedingJoinPoint->getInstance();
        $originalInstance->yourFunction();

        // Obtain annotation metadata
        /** @var \Hyperf\Di\Aop\AnnotationMetadata **/
        $metadata = $proceedingJoinPoint->getAnnotationMetadata();

        // Call the original method unaffected by the proxy class
        $proceedingJoinPoint->processOriginalMethod();

        // Do not execute the original method, perform other operations
        $result = date('YmdHis', time() - 86400);
        return $result;
    }
}
```

> Note: The class obtained by `getInstance` is a proxy class, and the methods inside it will still be affected by other aspects. Nested calls to each other will cause infinite loops and exhaust memory.

## Proxy Class Cache

All classes affected by AOP will generate corresponding `Proxy class cache` in the `./runtime/container/proxy/` folder. Whether it is automatically generated at startup depends on the value of the `scan_cacheable` configuration item in the `config/config.php` configuration file. The default value is `false`. If this configuration item is `true`, Hyperf will not scan and generate proxy class cache, but directly use the existing cache file as the final proxy class. If this configuration item is `false`, Hyperf will scan the annotation scanning domain every time the application starts and automatically generate the corresponding proxy class cache. When the code changes, the proxy class cache will also be automatically regenerated.

Usually, in the development environment, this value is `false`, which is more convenient for development and debugging. When deploying in the production environment, we may want Hyperf to generate all proxy classes in advance, rather than dynamically generating them when used. You can generate all proxy classes through the `php bin/hyperf.php` command, and then modify the value of this configuration item by setting the environment variable `SCAN_CACHEABLE` to `true`, so as to achieve the purpose of shorter startup time and lower application memory occupation.

Based on the above, if you use virtualization technologies such as Docker or Kubernetes to deploy your application, you can generate the corresponding proxy class cache during the image construction phase and write it into the image. When running the image instance, it can greatly reduce the startup time and application memory.
