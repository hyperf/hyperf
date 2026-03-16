# AOP 面向切面程式設計

## 概念

AOP 為 `Aspect Oriented Programming` 的縮寫，意為：`面向切面程式設計`，透過動態代理等技術實現程式功能的統一維護的一種技術。AOP 是 OOP 的延續，也是 Hyperf 中的一個重要內容，是函數語言程式設計的一種衍生範型。利用 AOP 可以對業務邏輯的各個部分進行隔離，從而使得業務邏輯各部分之間的耦合度降低，提高程式的可重用性，同時提高了開發的效率。

用通俗的話來講，就是在 Hyperf 裡可以透過 `切面(Aspect)` 介入到任意類的任意方法的執行流程中去，從而改變或加強原方法的功能，這就是 AOP。

> 注意這裡所指的任意類並不是完全意義上的所有類，在 Hyperf 啟動初期用於實現 AOP 功能的類自身不能被切入。

## 介紹

相對於其它框架實現的 AOP 功能的使用方式，我們進一步簡化了該功能的使用不做過細的劃分，僅存在 `環繞(Around)` 一種通用的形式：

- `切面(Aspect)` 為對流程織入的定義類，包括要介入的目標，以及實現對原方法的修改加強處理
- `代理類(ProxyClass)` ，每個被介入的目標類最終都會生成一個代理類，來達到執行 `切面(Aspect)` 方法的目的

## 定義切面(Aspect)

每個 `切面(Aspect)` 必須實現 `Hyperf\Di\Aop\AroundInterface` 介面，並提供 `public` 的 `$classes` 和 `$annotations` 屬性，為了方便使用，我們可以透過繼承 `Hyperf\Di\Aop\AbstractAspect` 來簡化定義過程，我們透過程式碼來描述一下。

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
    // 要切入的類或 Trait，可以多個，亦可透過 :: 標識到具體的某個方法，透過 * 可以模糊匹配
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
        // $proceedingJoinPoint 為連線點，透過該類的 process() 方法呼叫原方法並獲得結果
        // 在呼叫前進行某些處理
        $result = $proceedingJoinPoint->process();
        // 在呼叫後進行某些處理
        return $result;
    }
}
```

每個 `切面(Aspect)` 必須定義 `#[Aspect]` 註解或在 `config/autoload/aspects.php` 內配置均可發揮作用。

> 使用 `#[Aspect]` 註解時需 `use Hyperf\Di\Annotation\Aspect;` 名稱空間；

您也可以透過 `#[Aspect]` 註解本身的屬性來完成切入目標的配置，透過下面註解的形式可以達到與上面的示例一樣的目的：

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
        // $proceedingJoinPoint 為連線點，透過該類的 process() 方法呼叫原方法並獲得結果
        // 在呼叫前進行某些處理
        $result = $proceedingJoinPoint->process();
        // 在呼叫後進行某些處理
        return $result;
    }
}
```

## 改變或加強原方法

另外您還可以透過獲取原例項、方法反射、提交引數、獲取註解等方式實現業務需求：

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

        // 獲取呼叫方法時提交的引數
        $arguments = $proceedingJoinPoint->getArguments(); // array

        // 獲取原類的例項並呼叫原類的其他方法
        $originalInstance = $proceedingJoinPoint->getInstance();
        $originalInstance->yourFunction();

        // 獲取註解元資料
        /** @var \Hyperf\Di\Aop\AnnotationMetadata **/
        $metadata = $proceedingJoinPoint->getAnnotationMetadata();

        // 呼叫不受代理類影響的原方法
        $proceedingJoinPoint->processOriginalMethod();

        // 不執行原方法，做其他操作
        $result = date('YmdHis', time() - 86400);
        return $result;
    }
}
```

> 注意：`getInstance`獲取到的類為代理類，裡面的方法仍會被其他切面影響，相互巢狀呼叫會死迴圈耗盡記憶體。

## 代理類快取

所有被 AOP 影響的類，都會在 `./runtime/container/proxy/` 資料夾內生成對應的 `代理類快取`，是否在啟動時自動生成取決於 `config/config.php` 配置檔案中 `scan_cacheable` 配置項的值，預設值為 `false`，如果該配置項為 `true` 則 Hyperf 不會掃描和生成代理類快取，而是直接以現有的快取檔案作為最終的代理類。如果該配置項為 `false`，則 Hyperf 會在每次啟動應用時掃描註解掃描域並自動的生成對應的代理類快取，當代碼發生變化時，代理類快取也會自動的重新生成。

通常在開發環境下，該值為 `false`，這樣更便於開發除錯，而在部署生產環境時，我們可能會希望 Hyperf 提前將所有代理類提前生成，而不是使用時動態的生成，可以透過 `php bin/hyperf.php` 命令來生成所有代理類，然後再透過環境變數 `SCAN_CACHEABLE` 為 `true` 修改該配置項的值，以達到啟動時間更短、應用記憶體佔用更低的目的。

基於以上，如果您使用 Docker 或 Kubernetes 等虛擬化技術來部署您的應用的話，您可以在映象構建階段就生成對應的代理類快取並寫入到映象中去，在執行映象例項時，可大大減少啟動時間和應用記憶體。
