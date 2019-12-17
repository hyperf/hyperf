# AOP 面向切面程式設計

## 概念

AOP 為 `Aspect Oriented Programming` 的縮寫，意為：`面向切面程式設計`，通過動態代理等技術實現程式功能的統一維護的一種技術。AOP 是 OOP 的延續，也是 Hyperf 中的一個重要內容，是函數語言程式設計的一種衍生範型。利用 AOP 可以對業務邏輯的各個部分進行隔離，從而使得業務邏輯各部分之間的耦合度降低，提高程式的可重用性，同時提高了開發的效率。   

用通俗的話來講，就是在 Hyperf 裡可以通過 `切面(Aspect)` 介入到由 [hyperf/di](https://github.com/hyperf/di) 管理的任意類的任意方法的執行流程中去，從而改變或加強原方法的功能，這就是 AOP。

> 使用 AOP 功能必須使用 [hyperf/di](https://github.com/hyperf/di) 來作為依賴注入容器

## 介紹

相對於其它框架實現的 AOP 功能的使用方式，我們進一步簡化了該功能的使用不做過細的劃分，僅存在 `環繞(Around)` 一種通用的形式：

- `切面(Aspect)` 為對流程織入的定義類，包括要介入的目標，以及實現對原方法的修改加強處理
- `代理類(ProxyClass)` ，每個被介入的目標類最終都會生成一個代理類，來達到執行 `切面(Aspect)` 方法的目的，而非通過原類

## 定義切面(Aspect)

每個 `切面(Aspect)` 必須實現 `Hyperf\Di\Aop\AroundInterface` 介面，並提供 `public` 的 `$classes` 和 `$annotations` 屬性，為了方便使用，我們可以通過繼承 `Hyperf\Di\Aop\AbstractAspect` 來簡化定義過程，我們通過程式碼來描述一下。

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
    // 要切入的類，可以多個，亦可通過 :: 標識到具體的某個方法，通過 * 可以模糊匹配
    public $classes = [
        SomeClass::class,
        'App\Service\SomeClass::someMethod',
        'App\Service\SomeClass::*Method',
    ];
    
    // 要切入的註解，具體切入的還是使用了這些註解的類，僅可切入類註解和類方法註解
    public $annotations = [
        SomeAnnotation::class,
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        // 切面切入後，執行對應的方法會由此來負責
        // $proceedingJoinPoint 為連線點，通過該類的 process() 方法呼叫原方法並獲得結果
        // 在呼叫前進行某些處理
        $result = $proceedingJoinPoint->process();
        // 在呼叫後進行某些處理
        return $result;
    }
}
```

每個 `切面(Aspect)` 必須定義 `@Aspect` 註解或在 `config/autoload/aspects.php` 內配置均可發揮作用。

> 使用 `@Aspect` 註解時需 `use Hyperf\Di\Annotation\Aspect;` 名稱空間；  

## 代理類快取

所有被 AOP 影響的類，都會在 `./runtime/container/proxy/` 資料夾內生成對應的 `代理類快取`，服務啟動時，如果類所對應的代理類快取存在，則不會重新生成直接使用快取，即使 `Aspect` 的切入範圍發生了改變。不存在時，則會自動重新生成新的代理類快取。   

在部署生產環境時，我們可能會希望 Hyperf 提前將所有代理類提前生成，而不是使用時動態的生成，可以通過 `php bin/hyperf.php di:init-proxy` 命令來生成所有代理類，該命令會忽視現有的代理類快取，全部重新生成。   

基於以上，我們可以將生成代理類的命令和啟動服務的命令結合起來，`vendor/bin/init-proxy.sh && php bin/hyperf.php start` 來達到自動重新生成所有代理類快取然後啟動服務的目的。
