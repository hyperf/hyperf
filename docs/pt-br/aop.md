# AOP (Aspect Oriented Programming)

## Conceito

AOP é a abreviação de `Aspect Oriented Programming`, uma técnica para obter manutenção unificada de funcionalidades do programa por meio de técnicas como proxy dinâmico. AOP é uma continuação de OOP e uma parte importante do Hyperf. É um paradigma derivado da programação funcional. AOP pode ser usado para isolar diferentes partes da lógica de negócio, reduzindo o acoplamento entre essas partes, melhorando a reutilização do programa e aumentando a eficiência de desenvolvimento.

Em termos simples: no Hyperf, você pode intervir na execução de qualquer método de qualquer classe gerenciada por [hyperf/di](https://github.com/hyperf/di) por meio de `Aspect`. Durante o processo, você pode alterar ou aprimorar a funcionalidade do método original — isso é AOP.

> Para usar AOP é necessário usar [hyperf/di](https://github.com/hyperf/di) como container de injeção de dependências.

## Introdução

Comparado ao recurso de AOP implementado por outros frameworks, simplificamos ainda mais o uso dessa funcionalidade. Não há uma divisão maior: existe apenas uma forma universal do tipo "Around":

- `Aspect` é uma classe de definição que é tecida no fluxo do código, incluindo a definição do alvo que será interceptado e a modificação do método original do alvo.
- `ProxyClass`: cada uma das classes-alvo interceptadas irá gerar uma classe proxy para alcançar o objetivo de executar o método do `Aspect`, em vez de passar a classe original.

## Definir Aspect

Cada `Aspect` deve implementar `Hyperf\Di\Aop\AroundInterface` e fornecer as propriedades `$classes` e `$annotations` como `public`. Para facilitar o uso, podemos simplificar herdando `Hyperf\Di\Aop\AbstractAspect` na classe do nosso aspect.

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
    // A classe a ser interceptada (cut in) pode ser múltipla, pode ser identificada por `::` para um método específico, ou usar * para correspondência aproximada (fuzzy matching)
    public array $classes = [
        SomeClass::class,
        'App\Service\SomeClass::someMethod',
        'App\Service\SomeClass::*Method',
    ];
    
    // As anotações a serem interceptadas (cut into) representam as classes que usam essas anotações. Só é possível interceptar anotações de classe e anotações de métodos de classe.
    public array $annotations = [
        SomeAnnotation::class,
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        // Após o Aspect ser aplicado, o método correspondente ficará sob responsabilidade deste método.
        // $proceedingJoinPoint é o ponto de junção (joining point); o método original é chamado pelo método process() da classe para obter o resultado.
        // Faça algo antes do método original
        $result = $proceedingJoinPoint->process();
        // Faça algo depois do método original
        return $result;
    }
}
```

Cada `Aspect` precisa definir a anotação `#[Aspect]` ou ser configurado em `config/autoload/aspects.php` para ser habilitado.

> Ao usar a anotação `#[Aspect]`, é necessário `use Hyperf\Di\Annotation\Aspect;`.

## Cache de Proxy Class

Todas as classes afetadas por AOP irão gerar o respectivo `proxy class cache` na pasta `./runtime/container/proxy/`. Quando o servidor inicia, se já existir o cache de proxy correspondente a uma classe, ele não será regenerado e o cache será usado diretamente — mesmo que o `Aspect` ou a `Business Class` tenham mudado. Quando o cache não existir, um novo cache de proxy será gerado automaticamente.

Ao implantar em produção, talvez você queira que o Hyperf gere todas as proxy classes antecipadamente, em vez de gerar dinamicamente em runtime. Todas as proxy classes podem ser geradas com o comando `php bin/hyperf.php di:init-proxy`. Esse comando ignora o cache existente e regenera tudo.

Com base nisso, você pode combinar o comando que gera proxy classes com o comando de start do servidor: `php bin/hyperf.php di:init-proxy && php bin/hyperf.php start`. Esse comando irá regenerar automaticamente todo o cache de proxy classes e então iniciar o servidor.
