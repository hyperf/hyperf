# AOP 面向切面編程

## 概念

AOP 為 `Aspect Oriented Programming` 的縮寫，意為：`面向切面編程`，通過動態代理等技術實現程序功能的統一維護的一種技術。AOP 是 OOP 的延續，也是 Hyperf 中的一個重要內容，是函數式編程的一種衍生範型。利用 AOP 可以對業務邏輯的各個部分進行隔離，從而使得業務邏輯各部分之間的耦合度降低，提高程序的可重用性，同時提高了開發的效率。

用通俗的話來講，就是在 Hyperf 裏可以通過 `切面(Aspect)` 介入到任意類的任意方法的執行流程中去，從而改變或加強原方法的功能，這就是 AOP。

> 注意這裏所指的任意類並不是完全意義上的所有類，在 Hyperf 啓動初期用於實現 AOP 功能的類自身不能被切入。

## 介紹

相對於其它框架實現的 AOP 功能的使用方式，我們進一步簡化了該功能的使用不做過細的劃分，僅存在 `環繞(Around)` 一種通用的形式：

- `切面(Aspect)` 為對流程織入的定義類，包括要介入的目標，以及實現對原方法的修改加強處理
- `代理類(ProxyClass)` ，每個被介入的目標類最終都會生成一個代理類，來達到執行 `切面(Aspect)` 方法的目的

## 定義切面(Aspect)

每個 `切面(Aspect)` 必須實現 `Hyperf\Di\Aop\AroundInterface` 接口，並提供 `public` 的 `$classes` 和 `$annotations` 屬性，為了方便使用，我們可以通過繼承 `Hyperf\Di\Aop\AbstractAspect` 來簡化定義過程，我們通過代碼來描述一下。

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
    // 要切入的類或 Trait，可以多個，亦可通過 :: 標識到具體的某個方法，通過 * 可以模糊匹配
    public array $classes = [
        SomeClass::class,
        'App\Service\SomeClass::someMethod',
        'App\Service\SomeClass::*Method',
    ];

    // 要切入的註解，具體切入的還是使用了這些註解的類，僅可切入類註解和類方法註解
    public array $annotations = [
        SomeAnnotation::class,
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        // 切面切入後，執行對應的方法會由此來負責
        // $proceedingJoinPoint 為連接點，通過該類的 process() 方法調用原方法並獲得結果
        // 在調用前進行某些處理
        $result = $proceedingJoinPoint->process();
        // 在調用後進行某些處理
        return $result;
    }
}
```

每個 `切面(Aspect)` 必須定義 `#[Aspect]` 註解或在 `config/autoload/aspects.php` 內配置均可發揮作用。

> 使用 `#[Aspect]` 註解時需 `use Hyperf\Di\Annotation\Aspect;` 命名空間；

您也可以通過 `#[Aspect]` 註解本身的屬性來完成切入目標的配置，通過下面註解的形式可以達到與上面的示例一樣的目的：

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
        // 切面切入後，執行對應的方法會由此來負責
        // $proceedingJoinPoint 為連接點，通過該類的 process() 方法調用原方法並獲得結果
        // 在調用前進行某些處理
        $result = $proceedingJoinPoint->process();
        // 在調用後進行某些處理
        return $result;
    }
}
```

## 改變或加強原方法

另外您還可以通過獲取原實例、方法反射、提交參數、獲取註解等方式實現業務需求：

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
        // 獲取當前方法反射原型
        /** @var \ReflectionMethod **/
        $reflect = $proceedingJoinPoint->getReflectMethod();

        // 獲取調用方法時提交的參數
        $arguments = $proceedingJoinPoint->getArguments(); // array

        // 獲取原類的實例並調用原類的其他方法
        $originalInstance = $proceedingJoinPoint->getInstance();
        $originalInstance->yourFunction();

        // 獲取註解元數據
        /** @var \Hyperf\Di\Aop\AnnotationMetadata **/
        $metadata = $proceedingJoinPoint->getAnnotationMetadata();

        // 調用不受代理類影響的原方法
        $proceedingJoinPoint->processOriginalMethod();

        // 不執行原方法，做其他操作
        $result = date('YmdHis', time() - 86400);
        return $result;
    }
}
```

> 注意：`getInstance`獲取到的類為代理類，裏面的方法仍會被其他切面影響，相互嵌套調用會死循環耗盡內存。

## 代理類緩存

所有被 AOP 影響的類，都會在 `./runtime/container/proxy/` 文件夾內生成對應的 `代理類緩存`，是否在啓動時自動生成取決於 `config/config.php` 配置文件中 `scan_cacheable` 配置項的值，默認值為 `false`，如果該配置項為 `true` 則 Hyperf 不會掃描和生成代理類緩存，而是直接以現有的緩存文件作為最終的代理類。如果該配置項為 `false`，則 Hyperf 會在每次啓動應用時掃描註解掃描域並自動的生成對應的代理類緩存，當代碼發生變化時，代理類緩存也會自動的重新生成。

通常在開發環境下，該值為 `false`，這樣更便於開發調試，而在部署生產環境時，我們可能會希望 Hyperf 提前將所有代理類提前生成，而不是使用時動態的生成，可以通過 `php bin/hyperf.php` 命令來生成所有代理類，然後再通過環境變量 `SCAN_CACHEABLE` 為 `true` 修改該配置項的值，以達到啓動時間更短、應用內存佔用更低的目的。

基於以上，如果您使用 Docker 或 Kubernetes 等虛擬化技術來部署您的應用的話，您可以在鏡像構建階段就生成對應的代理類緩存並寫入到鏡像中去，在運行鏡像實例時，可大大減少啓動時間和應用內存。
