# View engine

> Reescrito com base na engine de template blade do laravel, suportando a sintaxe da engine de template blade original.

```bash
composer require hyperf/view-engine
```

## Gerar configuração

```bash
php bin/hyperf.php vendor:publish hyperf/view-engine
```

A configuração padrão é a seguinte

> Este componente recomenda o uso do modo de renderização SYNC, que pode reduzir efetivamente a perda de comunicação entre processos

```php
return [
    'engine' => Hyperf\ViewEngine\HyperfViewEngine::class,
    'mode' => Hyperf\View\Mode::SYNC,
    'config' => [
        'view_path' => BASE_PATH.'/storage/view/',
        'cache_path' => BASE_PATH.'/runtime/view/',
    ],

    # Custom component registration
    'components' => [
        //'alert' => \App\View\Components\Alert::class
    ],

    # View namespace (mainly used in extension packages)
    'namespaces' => [
        //'admin' => BASE_PATH.'/storage/view/vendor/admin',
    ],
];
```

## Uso

> Este tutorial toma como base em grande parte o conteúdo do [LearnKu](https://learnku.com), e sou muito grato ao LearnKu por sua contribuição para a comunidade PHP.

### Introdução

`Blade` é uma engine de template simples e poderosa fornecida pelo `Laravel`. Diferente de outras engines de template `PHP` populares, `Blade` não impede que você use código `PHP` nativo nas views.
Todos os arquivos de view `Blade` serão compilados em código `PHP` nativo e armazenados em cache; a menos que sejam modificados, não serão recompilados, o que significa que `Blade` basicamente não adiciona nenhuma sobrecarga à sua aplicação.
O arquivo de view `Blade` usa `.blade.php` como extensão de arquivo e é armazenado por padrão no diretório `storage/view`.

### Herança de template

#### Definir o layout

Primeiro, vamos estudar um layout de página "principal". Como a maioria das aplicações `web` usará o mesmo layout em diferentes páginas, é fácil definir uma única view de layout `Blade`:

```blade
<!-- Stored in storage/view/layouts/app.blade.php -->

<html>
    <head>
        <title>App Name-@yield('title')</title>
    </head>
    <body>
        @section('sidebar')
            This is the master sidebar.
        @show

        <div class="container">
            @yield('content')
        </div>
    </body>
</html>
```

Como você pode ver, este programa contém `HTML` comum. Mas preste atenção às instruções `@section` e `@yield`. Assim como o significado de `section`, para uma seção, a instrução `@section` define o conteúdo da seção, e a instrução `@yield` é usada para exibir o conteúdo da seção.

Agora que definimos o layout desta aplicação, a seguir, vamos definir uma subpágina que herda esse layout.

#### Herança de layout

Ao definir uma subview, use a instrução `@extends` do `Blade` para especificar a view que a subview deve "herdar". Views que estendem de um layout `Blade` podem usar a instrução `@section` para injetar conteúdo nas seções do layout.
Como mostrado no exemplo anterior, o conteúdo desses fragmentos será controlado e exibido pela instrução `@yield` no layout:

```blade
<!-- Stored in storage/view/child.blade.php -->

@extends('layouts.app')

@section('title','Page Title')

@section('sidebar')
    @parent

    <p>This is appended to the master sidebar.</p>
@endsection

@section('content')
    <p>This is my body content.</p>
@endsection
```

Neste exemplo, o fragmento `sidebar` usa a instrução `@parent` para acrescentar (não sobrescrever) conteúdo à `sidebar` do layout. Ao renderizar a view, a instrução `@parent` será substituída pelo conteúdo do layout.

> Contrariamente ao exemplo anterior, o fragmento sidebar aqui termina com @endsection em vez de @show. A instrução @endsection define apenas uma seção, enquanto @show a exibe imediatamente ao definí-la.

O comando `@yield` também aceita um valor padrão como segundo parâmetro. Se o fragmento "yield" não estiver definido, o valor padrão será renderizado:

```blade
@yield('content','Hyperf')
```

A view `Blade` pode ser retornada pela função auxiliar `Hyperf\ViewEngine\view`:

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Annotation\AutoController;
use function Hyperf\ViewEngine\view;

#[AutoController(prefix: "view")]
class ViewController extends AbstractController
{
    public function child()
    {
        return (string) view('child');
    }
}

```

### Exibindo dados

Você pode colocar variáveis entre chaves duplas para exibir dados na view. Por exemplo, dada a seguinte rota:

```php
use Hyperf\HttpServer\Router\Router;
use function Hyperf\ViewEngine\view;

Router::get('greeting', function () {
    return view('welcome', ['name' =>'Samantha']);
});
```

Você pode exibir o conteúdo da variável `name` da seguinte forma:

```blade
Hello, {{ $name }}.
```

> A instrução {{ }} do Blade será automaticamente escapada pela função htmlspecialchars do PHP para prevenir ataques XSS.

Não apenas você pode exibir o conteúdo das variáveis passadas para a view, você também pode exibir o resultado de qualquer função `PHP`. Na verdade, você pode colocar qualquer código PHP na instrução de echo do template Blade:

```blade
The current UNIX timestamp is {{ time() }}.
```

#### Exibir caracteres não escapados

Por padrão, instruções `Blade {{ }}` serão automaticamente escapadas pela função `htmlspecialchars` do `PHP` para prevenir ataques `XSS`. Se você não quiser que seus dados sejam escapados, você pode usar a seguinte sintaxe:

```blade
Hello, {!! $name !!}.
```

> Tenha muito cuidado ao exibir dados fornecidos pelo usuário na aplicação. Use o máximo possível a sintaxe de escape com chaves duplas para evitar ataques XSS.

#### Renderizar JSON

Às vezes, para inicializar uma variável `JavaScript`, você pode passar um array para a view e renderizá-lo como `JSON`. Ex.:

```
<script>
    var app = <?php echo json_encode($array); ?>;
</script>
```

Claro, você também pode usar a instrução `@json` do `Blade` em vez de chamar manualmente o método `json_encode`. Os parâmetros da instrução `@json` são os mesmos da função `json_encode` do `PHP`:

```html
<script>
    var app = @json($array);

    var app = @json($array, JSON_PRETTY_PRINT);
</script>
```

> Ao usar a diretiva @json, você deve renderizar como JSON apenas variáveis existentes. O template Blade é baseado em expressões regulares. Tentar passar uma expressão complexa para a diretiva @json pode causar erros imprevisíveis.

#### Codificação de entidades HTML

Por padrão, `Blade` codifica duplamente entidades `HTML`. Se você quiser desabilitar isso, você pode escutar o evento `BootApplication` e chamar o método `Blade::withoutDoubleEncoding`:

```php
<?php

declare(strict_types=1);

namespace App\Listener;

use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\ViewEngine\Blade;
use Psr\Container\ContainerInterface;

#[Listener]
class BladeWithoutDoubleEncodingListener implements ListenerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function listen(): array
    {
        return [
            BootApplication::class
        ];
    }

    public function process(object $event)
    {
        Blade::withoutDoubleEncoding();
    }
}

```

#### Blade & framework JavaScript

Como muitos frameworks JavaScript também usam "chaves duplas" para identificar expressões que serão exibidas no navegador, você pode usar o símbolo @ para indicar que a engine de renderização Blade não deve interferir. Ex.:

```blade
<h1>Laravel</h1>

Hello, @{{ name }}.
```

Neste exemplo, o símbolo `@` será removido pelo `Blade`; claro, o `Blade` não modificará a expressão `{{ name }}`, e sim o template `JavaScript` que a renderizará.
O símbolo `@` também é usado para escapar a instrução `Blade`:

```
{{-- Blade --}}
@@json()

<!-- HTML output -->
@json()
```

Se você exibir uma grande parte das variáveis `JavaScript` no template, você pode embutir `HTML` na diretiva `@verbatim`, para que não precise adicionar o símbolo `@` antes de cada instrução de echo do `Blade`:

```blade
@verbatim
    <div class="container">
        Hello, {{ name }}.
    </div>
@endverbatim
```

### Controle de fluxo

Além da herança de templates e da exibição de dados, `Blade` também fornece atalhos convenientes para estruturas de controle `PHP` comuns, como instruções condicionais e loops. Esses atalhos fornecem uma forma muito clara e concisa de escrever a estrutura de controle `PHP`. Ao mesmo tempo, também mantém características gramaticais semelhantes à estrutura de controle no `PHP`.

#### Instrução If

Você pode usar as instruções `@if`, `@elseif`, `@else` e `@endif` para construir instruções `if`. As funções desses comandos são exatamente as mesmas de suas respectivas instruções `PHP`:

```blade
@if (count($records) === 1)
    I have one record!
@elseif (count($records)> 1)
    I have multiple records!
@else
    I don't have any records!
@endif
```

Por conveniência, `Blade` também fornece uma instrução `@unless`:

```blade
@unless (is_signed_in())
    You are not signed in.
@endunless
```

Além das instruções condicionais já discutidas, as instruções `@isset` e `@empty` também podem ser usadas como atalhos para suas respectivas funções `PHP`:

```blade
@isset($records)
    // $records has been defined but not empty
@endisset

@empty($records)
    // $records is empty...
@endempty
```

#### Instruções de bloco

Você pode usar o comando `@hasSection` para determinar se o bloco contém conteúdo:

```blade
@hasSection('navigation')
    <div class="pull-right">
        @yield('navigation')
    </div>

    <div class="clearfix"></div>
@endif
```

Você pode usar o comando `@sectionMissing` para determinar se o bloco não possui conteúdo:

```blade
@sectionMissing('navigation')
    <div class="pull-right">
        @include('default-navigation')
    </div>
@endif
```

#### Instruções de ambiente

Você pode usar o comando `@production` para determinar se a aplicação está em ambiente de produção:

```blade
@production
    // Production environment specific content...
@endproduction
```

Ou, você pode usar o comando `@env` para determinar se a aplicação está sendo executada em um ambiente especificado:

```blade
@env('staging')
    // The application is running in the "staging" environment...
@endenv

@env(['staging','production'])
    // The application is running in a "staging" environment or a production environment...
@endenv
```

#### Instrução Switch

Você pode usar as instruções `@switch`, `@case`, `@break`, `@default` e `@endswitch` para construir uma instrução `Switch`:

```blade
@switch($i)
    @case(1)
        First case...
        @break

    @case(2)
        Second case...
        @break

    @default
        Default case...
@endswitch
```

#### Loop

Além das instruções condicionais, `Blade` também fornece instruções com as mesmas funções que a estrutura de loop do `PHP`. Da mesma forma, as funções dessas instruções são consistentes com sua sintaxe `PHP` correspondente:

```blade
@for ($i = 0; $i <10; $i++)
    The current value is {{ $i }}
@endfor

@foreach ($users as $user)
    <p>This is user {{ $user->id }}</p>
@endforeach

@forelse ($users as $user)
    <li>{{ $user->name }}</li>
@empty
    <p>No users</p>
@endforelse

@while (true)
    <p>I'm looping forever.</p>
@endwhile
```

> Ao fazer loop, você pode usar variáveis de loop para obter informações valiosas sobre o loop, por exemplo, se você está na primeira iteração ou na última iteração do loop.

Ao usar um loop, você pode encerrar o loop ou pular a iteração atual:

```blade
@foreach ($users as $user)
    @if ($user->type == 1)
        @continue
    @endif

    <li>{{ $user->name }}</li>

    @if ($user->number == 5)
        @break
    @endif
@endforeach
```

Você pode declarar uma instrução condicional em uma única linha da instrução:

```blade
@foreach ($users as $user)
    @continue($user->type == 1)

    <li>{{ $user->name }}</li>

    @break($user->number == 5)
@endforeach
```

#### Variável de loop

Ao fazer loop, a variável `$loop` pode ser usada dentro do loop. Essa variável fornece uma forma de acessar algumas informações, como o índice atual do loop e se essa iteração é a primeira ou a última vez:

```blade
@foreach ($users as $user)
    @if ($loop->first)
        This is the first iteration.
    @endif

    @if ($loop->last)
        This is the last iteration.
    @endif

    <p>This is user {{ $user->id }}</p>
@endforeach
```

Se você estiver em um loop aninhado, você pode acessar o loop pai usando a propriedade `parent` da variável `$loop` do loop:

```blade
@foreach ($users as $user)
    @foreach ($user->posts as $post)
        @if ($loop->parent->first)
            This is first iteration of the parent loop.
        @endif
    @endforeach
@endforeach
```

A variável `$loop` também contém vários atributos úteis:

| Propriedades | Observações |
|:--:|:--:|
| `$loop->index` | O índice da iteração atual (começando em 0). |
| `$loop->iteration` | O número de iterações do loop atual (começando em 1). |
| `$loop->remaining` | O número de iterações restantes do loop. |
| `$loop->count` | O número de elementos no array a ser iterado. |
| `$loop->first` | Se a iteração atual é a primeira iteração do loop. |
| `$loop->last` | Se a iteração atual é a última iteração do loop. |
| `$loop->even` | Se o número de iterações do loop atual é par. |
| `$loop->odd` | Se o número de iterações do loop atual é ímpar. |
| `$loop->depth` | A profundidade de aninhamento do loop atual. |
| `$loop->parent` | O loop pai no loop aninhado. |

#### Comentário

`Blade` também permite que você defina comentários na view. Mas, diferente dos comentários `HTML`, os comentários `Blade` não serão incluídos no `HTML` retornado pela aplicação:

```blade
{{-- This comment will not be present in the rendered HTML --}}
```

#### PHP

Em muitos casos, é útil embutir código `PHP` na sua view. Você pode usar a instrução `@php` do `Blade` no template para executar um bloco de código `PHP` nativo:

```blade
@php
    //
@endphp
```

> Embora o Blade forneça esse recurso, o uso frequente dele pode fazer com que muita lógica seja embutida nos seus templates.

#### Diretiva @once

A diretiva `@once` permite que você defina uma parte do conteúdo do template, que só será calculada uma vez em cada ciclo de renderização.
Essa instrução é muito útil no contexto de usar a `stack` para enviar um código `JavaScript` específico para o head da página.
Por exemplo, se você quiser renderizar um `component` específico em um loop, você pode querer enviar o código `JavaScript` para o head apenas na primeira vez que o component for renderizado:

```blade
@once
    @push('scripts')
        <script>
            // Your custom JavaScript code
        </script>
    @endpush
@endonce
```

### Components e slots

O papel de components e slots é semelhante ao de Section e Layout. No entanto, algumas pessoas podem achar que components e slots são mais convenientes de usar. Hyperf suporta duas formas de escrever components: components baseados em classe e components anônimos.

Podemos definir um component de classe criando uma classe que herda de `\Hyperf\ViewEngine\Component\Component::class`. A seguir mostraremos como usar o component criando um simples component `Alert`.

> app/View/Compoent/Alert.php

```php
<?php
namespace App\View\Component;
use Hyperf\ViewEngine\Component\Component;
use function Hyperf\ViewEngine\view;
class Alert extends Component
{
    public $type;
    public $message;
    public function __construct($type, $message)
    {
        $this->type = $type;
        $this->message = $message;
    }
    public function render()
    {
        retnurn view('components.alert');
    }
}
```

> storage/view/components/alert.blade.php
```html
<div class="alert alert-{{ $type }}">
    {{ $message }}
</div>
```

#### Registrar components manualmente

Em `config/autoload/view.php`

```php
<?php
return [
    // ...
    'components' => [
        'alert' => \App\View\Component\Alert::class,
    ],
];
```

Ou no `ConfigProvider` do pacote de extensão

```php
<?php
class ConfigProvider
{
    public function __invoke()
    {
        return [
            // ...others config
            'view' => [
                // ...others config
                'components' => [
                    'package-alert' => \App\View\Component\Alert::class,
                ],
            ],
        ];
    }
}
```

Após registrar o component, você poderá usá-lo através de aliases de tags HTML:

```html
<x-alert/>
<x-package-alert/>
```

#### Exibir components

Você pode usar tags de component Blade em qualquer template Blade para exibir components. A tag de component Blade começa com `x-`, seguida pelo nome do component.

```html
<x-alert/>
<x-package-alert/>
```

#### Transferência de parâmetros do component

Você pode usar atributos HTML para passar dados para o component Blade. Valores comuns podem ser passados por meio de atributos HTML simples, enquanto expressões e variáveis PHP devem ser passadas por meio de atributos prefixados com `:`:

```html
<x-alert type="error" :message="$message"/>
```

!> Nota: você pode definir os dados exigidos pelo component no construtor da classe do component. Todas as propriedades públicas na classe do component serão automaticamente passadas para a view do component. Não é necessário passá-las através do método `render` da classe do component. Ao renderizar um component, você pode obter o conteúdo das propriedades públicas da classe do component através do nome da variável.

#### Método do component

Além de obter as propriedades públicas da classe do component, você também pode executar qualquer método público da classe do component na view do component. Por exemplo, um component tem um método `isSelected`:

```php
    /**
     * Determine whether the given option is the current option
     *
     * @param string $option
     * @return bool
     */
    public function isSelected($option)
    {
        return $option === $this->selected;
    }
```

Você pode executar o método chamando a variável com o mesmo nome do método:

```html
    <option {{ $isSelected($value)?'selected="selected"':'' }} value="{{ $value }}">
        {{ $label }}
    </option>
```

#### Dependências adicionais

Se o seu component precisar depender de outras classes, você deve listá-las antes de todos os atributos de dados do component, e elas serão automaticamente injetadas pelo container:
```php
    use App\AlertCreator;
    /**
     * Create component instance
     *
     * @param \App\AlertCreator $creator
     * @param string $type
     * @param string $message
     * @return void
     */
    public function __construct(AlertCreator $creator, $type, $message)
    {
        $this->creator = $creator;
        $this->type = $type;
        $this->message = $message;
    }
```

#### Gerenciar propriedades

Já vimos como passar atributos de dados para components. No entanto, às vezes podemos precisar especificar outros atributos HTML (como `class`), que não são dados exigidos pelo component. Nesse caso, vamos querer passar esses atributos para o elemento raiz do template do component. Por exemplo, queremos renderizar um component alert da seguinte forma:

```html
    <x-alert type="error" :message="$message" class="mt-4"/>
```

Todas as propriedades que não fazem parte do construtor do component serão automaticamente adicionadas ao "pacote de propriedades" do component. O pacote de atributos será passado para a view do component através da variável `$attributes`. Ao exibir essa variável, todas as propriedades podem ser renderizadas no component:

```html
    <div {{ $attributes }}>
        <!-- Component content -->
    </div>
```

#### Obter atributos

Você pode usar o método `get()` para obter um valor de atributo específico. Este método aceita o nome do atributo como primeiro parâmetro (o segundo parâmetro é o valor padrão) e retorna seu valor.

```html
    <div class="{{ $attributes->get("class", "default") }}">
        <!-- Component content -->
    </div>
```

#### Detectar atributos

Você pode usar o método `has()` para obter um valor de atributo específico. Este método aceita o nome do atributo como parâmetro e retornará um valor booleano.

```html
    @if($attributes->has("class"))
        <div class="{{ $attributes->get("class") }}">
            <!-- Component content -->
        </div>
    @endif
```

#### Mesclar atributos

Em algum momento, você pode precisar especificar o valor padrão de um atributo, ou incorporar outros valores a determinados atributos do component. Para isso, você pode usar o método `merge` do pacote de propriedades:

```html
    <div {{ $attributes->merge(['class' =>'alert alert-'.$type]) }}>
        {{ $message }}
    </div>
```

Suponha que usemos este component como mostrado abaixo:

```html
    <x-alert type="error" :message="$message" class="mb-4"/>
```

O HTML final do component renderizado ficará assim:

```html
    <div class="alert alert-error mb-4">
        <!-- The content of the $message variable -->
    </div>
```

Por padrão, apenas os atributos `class` serão mesclados, e outros atributos serão diretamente sobrescritos. A seguinte situação ocorrerá:

```blade
// definition
<div {{ $attributes->merge(['class' =>'alert alert-'.$type,'other-attr' =>'foo']) }}>{{ $message }}</div>
// use
<x-alert type="error" :message="$message" class="mb-4" other-attr="bar"/>
// present
<div class="alert alert-error mb-4" other-attr="bar"><!-- The content of the $message variable --></div>
```

Como no caso acima, se você precisar mesclar os atributos `other-attr`, você pode usar o seguinte método para adicionar o segundo parâmetro `true` ao método `merge()`:

```blade
// definition
<div {{ $attributes->merge(['class' =>'alert alert-'.$type,'other-attr' =>'foo'], true) }}>{{ $message }}</ div>
// use
<x-alert type="error" :message="$message" class="mb-4" other-attr="bar"/>
// present
<div class="alert alert-error mb-4" other-attr="foo bar"><!-- The content of the $message variable --></div>
```

#### Slot

Geralmente, você precisa passar conteúdo adicional para o component através de `slots`. Suponha que o component alert que criamos tenha a seguinte marcação:

```html
    <!-- /storage/view/components/alert.blade.php -->

    <div class="alert alert-danger">
        {{ $slot }}
    </div>
```

Podemos passar conteúdo para os `slots` injetando conteúdo no component:

```html
    <x-alert>
        <strong>Whoops!</strong> Something went wrong!
    </x-alert>
```

Às vezes um component pode precisar colocar múltiplos slots diferentes em posições diferentes dentro dele. Vamos modificar o component alert para permitir a injeção de `title`.

```html
    <!-- /storage/view/components/alert.blade.php -->

    <span class="alert-title">{{ $title }}</span>

    <div class="alert alert-danger">
        {{ $slot }}
    </div>
```

Você pode usar a tag `x-slot` para definir o conteúdo de um slot nomeado. Outros conteúdos que não estejam na tag `x-slot` serão passados para os components na variável `$slot`:

```html
    <x-alert>
        <x-slot name="title">
            Server Error
        </x-slot>

        <strong>Whoops!</strong> Something went wrong!
    </x-alert>
```

#### Components inline

Para components pequenos, gerenciar classes de component e templates de view de component pode ser um problema. Por isso, você pode retornar o conteúdo do component a partir do método `render`:

```php
    public function render()
    {
        return <<<'blade'
            <div class="alert alert-danger">
                {{ $slot }}
            </div>blade;
    }
```

#### Components anônimos

Assim como os components inline, os components anônimos fornecem um mecanismo para gerenciar components através de um único arquivo. No entanto, o component anônimo usa um único arquivo de view sem classes associadas. Para definir um component anônimo, você só precisa colocar o template Blade no diretório `/storage/view/components`.
Por exemplo, suponha que você defina um component em `/storage/view/components/alert.blade.php`:

```html
    <x-alert/>
```

Se o component estiver em um subdiretório do diretório `components`, você pode usar o caractere `.` para especificar seu caminho. Por exemplo, se o component estiver definido em `/storage/view/components/inputs/button.blade.php`, você pode renderizá-lo assim:

```html
    <x-inputs.button/>
```

#### Dados e atributos de components anônimos

Como os components anônimos não possuem nenhuma classe associada, você pode querer distinguir quais dados devem ser passados para o component como variáveis e quais propriedades devem ser armazenadas no [pacote de propriedades](#Manage Attribute).

Você pode usar a diretiva @props no nível superior do template Blade do component para especificar quais propriedades devem ser usadas como variáveis de dados. Todas as outras propriedades do component serão fornecidas na forma de um pacote de propriedades. Se você quiser especificar um valor padrão para uma determinada variável de dados, você pode usar o nome do atributo como chave do array e o valor padrão como valor do array:

```blade
    <!-- /storage/view/components/alert.blade.php -->

    @props(['type' =>'info','message'])

    <div {{ $attributes->merge(['class' =>'alert alert-'.$type]) }}>
        {{ $message }}
    </div>
```

#### Components dinâmicos

Às vezes, você pode precisar renderizar um component, mas não sabe qual renderizar antes de executar. Nesse caso, você pode usar o component embutido `dynamic-component` para renderizar um component com base em valores ou variáveis:

```html
    <x-dynamic-component :component="$componentName" class="mt-4" />
```

#### Carregamento automático de components

Por padrão, components sob `App\View\Component\` e `components.` são registrados automaticamente. Você também pode modificar essa configuração através do arquivo de configuração:

> config/autoload/view.php

```php
return [
    // ...
    'autoload' => [
        'classes' => ['App\\Other\\Component\\','App\\Another\\Component\\'],
        'components' => ['package::components.','components.'],
    ],
];
```

## Espaço de view

Ao definir o espaço de view, você pode facilmente usar o arquivo de view no seu pacote de extensão. Você só precisa adicionar uma linha de configuração no `ConfigProvider`:

```php
<?php
class ConfigProvider
{
    public function __invoke()
    {
        return [
            // ...others config
            'view' => [
                // ...others config
                'namespaces' => [
                    'package-name' => __DIR__.'/../views',
                ],
            ],
        ];
    }
}
```

Após instalar o pacote de extensão, você pode sobrescrever a view do pacote de extensão definindo um arquivo de view com o mesmo caminho em `/storage/view/vendor/package-name` do projeto.

## Middleware opcional

- Hyperf\ViewEngine\Http\Middleware\ShareErrorsFromSession::class

Compartilha automaticamente os `errors` na `session` com a view, dependendo do componente `hyperf/session`

- Hyperf\ViewEngine\Http\Middleware\ValidationExceptionHandle::class

Captura automaticamente exceções em `validation` e as adiciona à `session`, dependendo dos componentes `hyperf/session` e `hyperf/validation`

## Outros comandos

Instalação automática das configurações relacionadas aos componentes `view-engine`, `translation` e `validation`

```
php bin/hyperf.php view:publish
```
