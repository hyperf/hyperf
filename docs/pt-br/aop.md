# AOP (Aspect Oriented Programming)

## Conceito

AOP é a abreviação de `Aspect Oriented Programming`, uma técnica para alcançar a manutenção unificada das funcionalidades do programa através de técnicas como dynamic proxy. AOP é uma continuação da OOP e uma parte importante do Hyperf. É um paradigma derivado da programação funcional. AOP pode ser usado para isolar as várias partes da lógica de negócio, o que reduz o grau de acoplamento entre as várias partes da lógica de negócio, melhora a reutilização do programa e aumenta a eficiência do desenvolvimento. 

Em termos simples, no Hyperf você pode intervir na execução de qualquer method de qualquer class gerenciada pelo [hyperf/di](https://github.com/hyperf/di) através de `Aspect`. Ao interferir no processo para alterar ou aprimorar a funcionalidade do method original, isso é AOP.

> Usar AOP requer o uso do [hyperf/di](https://github.com/hyperf/di) como o container de Dependency Injection

## Introdução

Em comparação com a funcionalidade de AOP implementada por outros frameworks, nós simplificamos ainda mais o uso dessa funcionalidade sem uma divisão maior; existe apenas uma forma universal de "Around":

- `Aspect` é uma class de definição que se entrelaça no fluxo de código, incluindo a definição do target a ser envolvido e a modificação do method original do target.
- `ProxyClass`, cada uma das classes target envolvidas eventualmente gerará uma proxy class para alcançar o propósito de executar o method do `Aspect`, em vez de passar pela class original.

## Definir Aspect

Cada `Aspect` precisa implementar `Hyperf\Di\Aop\AroundInterface`, e fornecer as properties `$classes` e `$annotations` em nível `public`. Para facilitar o uso, podemos simplificar o uso herdando `Hyperf\Di\Aop\AbstractAspect` em nossa aspect class.

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
    // The class to be cut in can be multiple, or can be identified by `::` to the specific method, or use * for fuzzy matching
    public array $classes = [
        SomeClass::class,
        'App\Service\SomeClass::someMethod',
        'App\Service\SomeClass::*Method',
    ];
    
    // The annotations to be cut into, means the classes that use these annotations to be cut into, can only cut into class annotations and class method annotations.
    public array $annotations = [
        SomeAnnotation::class,
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        // After the Aspect is cut into, the corresponding method will be responsible by this method.
        // $proceedingJoinPoint is the joining point, the original method is called by the process() method of the class and obtain the result.
        // Do something before original method
        $result = $proceedingJoinPoint->process();
        // Do something after original method
        return $result;
    }
}
```

Cada `Aspect` precisa definir a annotation `#[Aspect]` ou ser configurado em `config/autoload/aspects.php` para ser habilitado.

> Para usar a annotation `#[Aspect]` é necessário `use Hyperf\Di\Annotation\Aspect;`  

## Cache da Proxy Class

Todas as classes afetadas pelo AOP gerarão o `cache de proxy class` correspondente na pasta `./runtime/container/proxy/`. Quando o servidor inicia, se o cache de proxy class correspondente à class já existir, ele não será regenerado, usando o cache diretamente, mesmo que o `Aspect` ou a `Business Class` tenha mudado. Quando o cache não estiver presente, um novo cache de proxy class será gerado automaticamente.

Ao fazer o deploy do ambiente de produção, podemos querer que o Hyperf gere todas as proxy classes com antecedência, em vez de as gerar dinamicamente em tempo de execução. Todas as proxy classes podem ser geradas pelo comando `php bin/hyperf.php di:init-proxy`. O comando ignora o cache de proxy class existente e o regenera completamente.

Com base no exposto, podemos combinar o comando de geração de proxy class com o comando de iniciar o servidor: `php bin/hyperf.php di:init-proxy && php bin/hyperf.php start`; esse comando regenerará automaticamente todo o cache de proxy classes e então iniciará o servidor.
