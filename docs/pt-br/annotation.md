# Anotações (Attributes)

Anotações/attributes são um recurso muito poderoso do Hyperf que pode ser usado para reduzir bastante configuração na forma de anotações e para implementar recursos muito convenientes.

## Conceito

### O que é uma annotation/attribute?

Attributes oferecem a capacidade de adicionar metadados estruturados e legíveis por máquina em declarações no código: classes, métodos, funções, parâmetros, propriedades e constantes de classe podem ser alvo de um attribute. Os metadados definidos por attributes podem então ser inspecionados em runtime usando as APIs de Reflection. Assim, attributes podem ser vistos como uma linguagem de configuração embutida diretamente no código.

Com attributes, a implementação genérica de um recurso e seu uso concreto em uma aplicação podem ser desacoplados. De certa forma, isso é comparável a interfaces e suas implementações. Mas enquanto interfaces e implementações tratam de código, attributes tratam de anotar informações extras e configuração. Interfaces podem ser implementadas por classes, e attributes podem ser declarados também em métodos, funções, parâmetros, propriedades e constantes de classe. Portanto, eles são mais flexíveis do que interfaces.

Um exemplo simples de uso de attribute é converter uma interface que tem métodos opcionais para usar attributes. Suponha uma interface ActionHandler que representa uma operação na aplicação, em que algumas implementações exigem setup e outras não. Em vez de exigir que todas as classes que implementam ActionHandler implementem um método setUp(), um attribute pode ser usado. Um benefício dessa abordagem é que podemos usar o attribute várias vezes.

### Como isso funciona?

Já vimos que anotações são apenas definições de metadados; para funcionarem, elas precisam ser usadas em conjunto com a aplicação. No Hyperf, os dados das anotações são coletados na classe `Hyperf\Di\Annotation\AnnotationCollector` para uso pela aplicação. Dependendo da sua necessidade, você também pode coletar os dados em classes customizadas e então ler/utilizar os metadados coletados no lugar onde as anotações devem funcionar, para alcançar a implementação desejada.

### Ignorar algumas anotações

Em alguns casos, podemos querer ignorar certas anotações. Por exemplo, ao usar ferramentas que geram documentação automaticamente, muitas delas usam anotações para definir a estrutura do documento. Essas anotações podem não se alinhar com a forma de uso do Hyperf. Podemos configurar o que deve ser ignorado em `config/autoload/annotations.php`.

```php
use JetBrains\PhpStorm\ArrayShape;

return [
    'scan' => [
        // Anotações no array ignore_annotations serão ignoradas pelo scanner de anotações
        'ignore_annotations' => [
            ArrayShape::class,
        ],
    ],
];
```

## Uso de anotações

Existem três tipos de aplicação de annotation: `classe`, `método da classe` e `propriedade da classe`.

### Usar annotation no nível de classe

Anotações no nível de classe são definidas no bloco acima da palavra-chave `class`. Por exemplo, `Controller` e `AutoController` são exemplos comuns de anotações de classe. O exemplo abaixo mostra o uso correto de uma annotation de classe, indicando que `ClassAnnotation` foi aplicada à classe `Foo`.

```php
<?php
#[ClassAnnotation]
class Foo {}
```

### Usar annotation no nível de método

Anotações no nível de método são definidas no bloco acima do método da classe. Por exemplo, `RequestMapping` é um exemplo comum de annotation de método. O exemplo abaixo mostra o uso correto de uma annotation de método, indicando que `MethodAnnotation` foi aplicada ao método `bar` da classe `Foo`.

```php
<?php
class Foo
{
    #[MethodAnnotation]
    public function bar()
    {
        // Código aqui
    }
}
```

### Usar annotation no nível de propriedade

Anotações no nível de propriedade são definidas no bloco acima da propriedade. Por exemplo, `Value` e `Inject` são exemplos comuns de annotation de propriedade. O exemplo abaixo mostra o uso correto de uma annotation de propriedade, indicando que `PropertyAnnotation` foi aplicada à propriedade `$bar` da classe `Foo`.

```php
<?php
class Foo
{
    #[PropertyAnnotation]
    private $bar;
}
```

### Passagem de parâmetros de annotation

- Passar o parâmetro único principal `#[DemoAnnotation('value')]`
- Passar parâmetros string `#[DemoAnnotation(key1: 'value1', key2: 'value2')]`
- Passar parâmetros array `#[DemoAnnotation(key: ['value1', 'value2'])]`

## Annotation customizada

### Criar uma classe de annotation

Crie uma classe de annotation em qualquer lugar, como no exemplo abaixo:

```php
<?php
namespace App\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Bar extends AbstractAnnotation
{
    // Código aqui
}

#[Attribute(Attribute::TARGET_CLASS)]
class Foo extends AbstractAnnotation
{
    // Código aqui
}
```

Note que no exemplo acima, a classe de annotation herda a classe abstrata `Hyperf\Di\Annotation\AbstractAnnotation`. Isso não é obrigatório para classes de annotation, mas no caso de classes de annotation do Hyperf, herdar a interface `Hyperf\Di\Annotation\AnnotationInterface` é obrigatório. O papel da classe abstrata aqui é fornecer uma definição mínima.

A classe abstrata já foi implementada para você para: `atribuir automaticamente parâmetros da annotation às propriedades da classe` e `coletar automaticamente os dados da annotation no AnnotationCollector`.

### Collector de annotations customizado

O fluxo de execução específico da coleta de annotations também é implementado na classe de annotation. O método relacionado é restrito por `Hyperf\Di\Annotation\AnnotationInterface`. A interface exige a implementação dos três métodos a seguir, e você pode implementar a lógica conforme sua necessidade:

- `public function collectClass(string $className): void;` Esse método é acionado quando a annotation é definida na classe.
- `public function collectMethod(string $className, ?string $target): void;` Esse método é acionado quando a annotation é definida em um método.
- `public function collectProperty(string $className, ?string $target): void` Esse método é acionado quando a annotation é definida em uma propriedade.

### Uso dos dados de annotation

Quando não há um método customizado de coleta, por padrão os metadados das annotations serão coletados na classe `Hyperf\Di\Annotation\AnnotationCollector`. Os métodos estáticos da classe facilitam obter os metadados correspondentes para decisões de lógica ou implementação.

## Plugin de IDE para annotations

Como o `PHP` não suporta `annotation` nativamente, `IDE`s não adicionam suporte a annotations por padrão. Porém, podemos adicionar plugins de terceiros para permitir que a `IDE` suporte anotações.

### PhpStorm

Você pode procurar por `PHP Annotations` em `Plugins` e encontrar o componente [PHP Annotations](https://github.com/Haehnchen/idea-php-annotation-plugin). Em seguida, instale o plugin e reinicie o `PhpStorm`. Assim, você pode usar anotações com mais conforto.

Esse plugin fornece principalmente recursos de salto automático e sugestões de código para classes de annotation e adiciona automaticamente o namespace correspondente quando anotações são usadas.
