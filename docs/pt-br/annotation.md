# Annotation

Annotation é um recurso muito poderoso do Hyperf, que pode ser usado para reduzir muitas configurações em forma de annotation e para implementar diversas funcionalidades bem convenientes.

## Conceito

### O que é annotation?

Attributes oferecem a capacidade de adicionar informações de metadados estruturados e legíveis por máquina em declarações no código: classes, métodos, funções, parâmetros, propriedades e constantes de classe podem ser o target de um attribute. Os metadados definidos por attributes podem então ser inspecionados em tempo de execução usando as Reflection APIs. Attributes podem, portanto, ser pensados como uma linguagem de configuração embutida diretamente no código.

Com attributes, a implementação genérica de uma funcionalidade e seu uso concreto em uma aplicação podem ser desacoplados. De certa forma, isso é comparável a interfaces e suas implementações. Mas, enquanto interfaces e implementações são sobre código, attributes são sobre anotar informações e configurações extras. Interfaces podem ser implementadas por classes, mas attributes também podem ser declarados em métodos, funções, parâmetros, propriedades e constantes de classe. Dessa forma, eles são mais flexíveis do que interfaces.

Um exemplo simples de uso de attribute é converter uma interface que possui métodos opcionais para usar attributes. Vamos supor uma interface ActionHandler representando uma operação em uma aplicação, onde algumas implementações de um action handler exigem setup e outras não. Em vez de exigir que todas as classes que implementam ActionHandler implementem um método setUp(), um attribute pode ser usado. Um benefício dessa abordagem é que podemos usar o attribute várias vezes.

### Como funciona?

Já dissemos que annotations são apenas definições de metadados que precisam trabalhar em conjunto com a aplicação para funcionar. No Hyperf, os dados das annotations são coletados na classe `Hyperf\Di\Annotation\AnnotationCollector` para uso pela aplicação; dependendo da sua necessidade, você também pode coletar os dados em suas próprias classes personalizadas e então ler e utilizar os metadados das annotations coletadas no local onde as próprias annotations devem funcionar, para alcançar a implementação funcional desejada.

### Ignorar algumas annotations

Em alguns casos, podemos querer ignorar determinadas annotations. Por exemplo, ao acessar algumas ferramentas que geram documentos automaticamente, muitas ferramentas usam annotations para definir o conteúdo estrutural relevante do documento. Essas annotations podem não estar alinhadas com a forma como o Hyperf é usado; podemos definir o que deve ser ignorado através do `config/autoload/annotations.php`.

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

## Uso de Annotation

Existem três tipos de aplicação de annotation: `class`, `method of class` e `property of class`.

### Uso de annotation em nível de class

As definições de annotation em nível de class ficam no bloco de comentário acima da palavra-chave `class`. Por exemplo, as annotations comumente usadas `Controller` e `AutoController` são exemplos do uso de annotation em nível de class. O exemplo de código a seguir é um exemplo do uso correto de annotation em nível de class, indicando que a annotation `ClassAnnotation` é aplicada à class `Foo`.

```php
<?php
#[ClassAnnotation]
class Foo {}
```

### Uso de annotation em nível de method

As definições de annotation em nível de method ficam no bloco de comentário acima do method da class. Por exemplo, a annotation comumente usada `RequestMapping` é um exemplo do uso de annotation em nível de method. O exemplo de código a seguir é um exemplo do uso correto de annotation em nível de method, indicando que a annotation `MethodAnnotation` é aplicada ao method `bar` da class `Foo`.

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

### Uso de annotation em nível de property

As definições de annotation em nível de property ficam no bloco de comentário acima da property. Por exemplo, as annotations comumente usadas `Value` e `Inject` são exemplos do uso de annotation em nível de property. O exemplo de código a seguir é um exemplo do uso correto de annotation em nível de property, indicando que a annotation `PropertyAnnotation` é aplicada à property `$bar` da class `Foo`.

```php
<?php
class Foo
{
    #[PropertyAnnotation]
    private $bar;
}
```

### Passagem de parâmetro de annotation

- Passar o parâmetro único principal `#[DemoAnnotation('value')]`
- Passar o parâmetro string `#[DemoAnnotation(key1: 'value1', key2: 'value2')]`
- Passar o parâmetro array `#[DemoAnnotation(key: ['value1', 'value2'])]`

## Annotation Customizada

### Criar uma class de Annotation

Crie uma class de annotation em qualquer lugar, como no exemplo de código a seguir:

```php
<?php
namespace App\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Bar extends AbstractAnnotation
{
    // some code
}

#[Attribute(Attribute::TARGET_CLASS)]
class Foo extends AbstractAnnotation
{
    // some code
}
```

Observe que, no código de exemplo acima, a class de annotation herda a abstract class `Hyperf\Di\Annotation\AbstractAnnotation`. Isso não é obrigatório para classes de annotation, mas para classes de annotation do Hyperf é obrigatório herdar a interface `Hyperf\Di\Annotation\AnnotationInterface`. Assim, o papel dessa abstract class aqui é fornecer uma definição mínima. A abstract class já foi implementada para você `atribuir automaticamente os parâmetros da annotation às properties da class` e `coletar automaticamente os dados da annotation no AnnotationCollector`.

### Annotation Collector Customizado

O fluxo de execução específico da coleta de annotation também é implementado na class de annotation. O method relacionado é restringido pela `Hyperf\Di\Annotation\AnnotationInterface`. A interface exige a implementação dos três methods a seguir, e você pode implementar a lógica correspondente de acordo com sua necessidade:

- `public function collectClass(string $className): void;` Este method é disparado quando a annotation é definida na class
- `public function collectMethod(string $className, ?string $target): void;` Este method é disparado quando a annotation é definida no method
- `public function collectProperty(string $className, ?string $target): void` Este method é disparado quando a annotation é definida na property

### Uso dos dados de annotation

Quando não há um method customizado de coleta de annotation, os metadados da annotation serão coletados na class `Hyperf\Di\Annotation\AnnotationCollector` por padrão. O method estático da class permite obter facilmente os metadados correspondentes para julgamento lógico ou implementação.

## Plugin de IDE para Annotation

Como o `PHP` não suporta `annotation` nativamente, a `IDE` não adiciona suporte à funcionalidade de annotation por padrão. Mas podemos adicionar plugins de terceiros para que a `IDE` suporte a funcionalidade de `annotation`.

### PhpStorm

Podemos buscar por `PHP Annotations` em `Plugins` e encontrar o componente correspondente [PHP Annotations](https://github.com/Haehnchen/idea-php-annotation-plugin). Depois instale o plugin, reinicie o `PhpStorm`, e você poderá usar a funcionalidade de annotation tranquilamente. Ele fornece principalmente os recursos de adicionar suporte a salto automático e sugestão de código para classes de annotation, além de referenciar automaticamente o namespace correspondente quando annotations são usadas.
