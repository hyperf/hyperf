# Motor de views

> Reescrito com base no mecanismo de templates Blade do Laravel, com suporte Ã  sintaxe do Blade original.

```bash
composer require hyperf/view-engine
```

## Gerar configuraÃ§Ã£o

```bash
php bin/hyperf.php vendor:publish hyperf/view-engine
```

A configuraÃ§Ã£o padrÃ£o Ã© a seguinte

> Este componente recomenda usar o modo de renderizaÃ§Ã£o SYNC, que pode reduzir efetivamente o custo da comunicaÃ§Ã£o entre processos

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

> Este tutorial foi fortemente baseado em conteÃºdo do [LearnKu](https://learnku.com), e sou muito grato ao LearnKu por sua contribuiÃ§Ã£o para a comunidade PHP.

### IntroduÃ§Ã£o

`Blade` Ã© um mecanismo de templates simples e poderoso fornecido pelo `Laravel`. Diferente de outros mecanismos de template populares em `PHP`, o `Blade` nÃ£o restringe o uso de cÃ³digo `PHP` nativo nas views.
Todos os arquivos de view `Blade` serÃ£o compilados para cÃ³digo `PHP` nativo e armazenados em cache; a menos que sejam modificados, eles nÃ£o serÃ£o recompilados, o que significa que o `Blade` basicamente nÃ£o adiciona sobrecarga ao seu aplicativo.
O arquivo de view `Blade` usa `.blade.php` como extensÃ£o e, por padrÃ£o, Ã© armazenado no diretÃ³rio `storage/view`.

### HeranÃ§a de templates

#### Definir o layout

Primeiro, vamos estudar um layout de pÃ¡gina "principal". Como a maioria dos aplicativos `web` usa o mesmo layout em pÃ¡ginas diferentes, Ã© fÃ¡cil definir uma Ãºnica view de layout `Blade`:

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

Como vocÃª pode ver, esse exemplo contÃ©m `HTML` comum. Mas preste atenÃ§Ã£o Ã s diretivas `@section` e `@yield`. Assim como o significado de `section`, para uma seÃ§Ã£o a diretiva `@section` define o conteÃºdo da seÃ§Ã£o, e a diretiva `@yield` Ã© usada para exibir o conteÃºdo da seÃ§Ã£o.

Agora que definimos o layout desta aplicaÃ§Ã£o, em seguida definimos uma subpÃ¡gina que herda esse layout.

#### HeranÃ§a de layout

Ao definir uma subview, use a diretiva `@extends` do `Blade` para especificar a view que a subview deve "herdar". Views que estendem o layout `Blade` podem usar a diretiva `@section` para injetar conteÃºdo na seÃ§Ã£o do layout.
Como mostrado no exemplo anterior, o conteÃºdo desses trechos serÃ¡ controlado e exibido pela diretiva `@yield` no layout:

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

Neste exemplo, o trecho `sidebar` usa a diretiva `@parent` para anexar (e nÃ£o sobrescrever) conteÃºdo ao `sidebar` do layout. Ao renderizar a view, a diretiva `@parent` serÃ¡ substituÃ­da pelo conteÃºdo no layout.

> Ao contrÃ¡rio do exemplo anterior, aqui o trecho do sidebar termina com @endsection em vez de @show. A diretiva @endsection define apenas uma seÃ§Ã£o, enquanto @show exibe essa seÃ§Ã£o imediatamente enquanto a define.

O comando `@yield` tambÃ©m aceita um valor padrÃ£o como segundo parÃ¢metro. Se o trecho "yield" nÃ£o estiver definido, o valor padrÃ£o Ã© renderizado:

```blade
@yield('content','Hyperf')
```

A view `Blade` pode ser retornada pela funÃ§Ã£o helper `Hyperf\ViewEngine\view`:

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

VocÃª pode colocar variÃ¡veis entre chaves para exibir dados na view. Por exemplo, dada a seguinte rota:

```php
use Hyperf\HttpServer\Router\Router;
use function Hyperf\ViewEngine\view;

Router::get('greeting', function () {
    return view('welcome', ['name' =>'Samantha']);
});
```

VocÃª pode exibir o conteÃºdo da variÃ¡vel `name` da seguinte forma:

```blade
Hello, {{ $name }}.
```

> A instruÃ§Ã£o `{{ }}` do Blade serÃ¡ automaticamente escapada pela funÃ§Ã£o `htmlspecialchars` do PHP para prevenir ataques de XSS.

AlÃ©m de exibir o conteÃºdo das variÃ¡veis passadas para a view, vocÃª tambÃ©m pode imprimir o resultado de qualquer funÃ§Ã£o `PHP`. Na prÃ¡tica, vocÃª pode colocar qualquer cÃ³digo PHP na instruÃ§Ã£o de echo do template Blade:

```blade
The current UNIX timestamp is {{ time() }}.
```

#### Exibir caracteres nÃ£o escapados

Por padrÃ£o, as instruÃ§Ãµes `Blade {{ }}` serÃ£o automaticamente escapadas pela funÃ§Ã£o `htmlspecialchars` do `PHP` para prevenir ataques de `XSS`. Se vocÃª nÃ£o quiser que seus dados sejam escapados, pode usar a seguinte sintaxe:

```blade
Hello, {!! $name !!}.
```

> Tenha muito cuidado ao exibir dados fornecidos pelo usuÃ¡rio no aplicativo. Use escaping e a sintaxe com chaves duplas sempre que possÃ­vel para prevenir ataques de XSS.

#### Renderizar JSON

Ã€s vezes, para inicializar uma variÃ¡vel `JavaScript`, vocÃª pode passar um array para a view e renderizÃ¡-lo como `JSON`. Por exemplo:

```
<script>
    var app = <?php echo json_encode($array); ?>;
</script>
```

Claro, vocÃª tambÃ©m pode usar o comando `Blade` `@json` em vez de chamar manualmente o mÃ©todo `json_encode`. Os parÃ¢metros da diretiva `@json` sÃ£o os mesmos da funÃ§Ã£o `json_encode` do `PHP`:

```html
<script>
    var app = @json($array);

    var app = @json($array, JSON_PRETTY_PRINT);
</script>
```

> Ao usar a diretiva @json, vocÃª deve renderizar apenas variÃ¡veis existentes como JSON. O template Blade Ã© baseado em expressÃµes regulares; tentar passar uma expressÃ£o complexa para a diretiva @json pode causar erros imprevisÃ­veis.

#### CodificaÃ§Ã£o de entidades HTML

Por padrÃ£o, o `Blade` farÃ¡ double-encode de entidades `HTML`. Se vocÃª quiser desabilitar isso, pode escutar o evento `BootApplication` e chamar o mÃ©todo `Blade::withoutDoubleEncoding`:

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

#### Blade e frameworks JavaScript

Como muitos frameworks JavaScript tambÃ©m usam "chaves" para identificar expressÃµes que serÃ£o exibidas no navegador, vocÃª pode usar o sÃ­mbolo @ para indicar que o Blade nÃ£o deve processar a expressÃ£o. Por exemplo:

```blade
<h1>Laravel</h1>

Hello, @{{ name }}.
```

Neste exemplo, o sÃ­mbolo `@` serÃ¡ removido pelo `Blade`; e, claro, o `Blade` nÃ£o modificarÃ¡ a expressÃ£o `{{ name }}` â€” isso fica para o template `JavaScript` renderizar.
O sÃ­mbolo `@` tambÃ©m Ã© usado para escapar diretivas do `Blade`:

```
{{-- Blade --}}
@@json()

<!-- HTML output -->
@json()
```

Se vocÃª exibir uma grande parte das variÃ¡veis `JavaScript` no template, pode embutir o `HTML` dentro da diretiva `@verbatim`, para nÃ£o precisar adicionar o sÃ­mbolo `@` antes de cada echo do `Blade`:

```blade
@verbatim
    <div class="container">
        Hello, {{ name }}.
    </div>
@endverbatim
```

### Controle de fluxo

AlÃ©m de heranÃ§a de templates e exibiÃ§Ã£o de dados, o `Blade` tambÃ©m fornece atalhos convenientes para estruturas de controle comuns do `PHP`, como condicionais e loops. Esses atalhos oferecem uma forma bem clara e concisa de escrever estruturas de controle do `PHP` e, ao mesmo tempo, mantÃªm caracterÃ­sticas gramaticais semelhantes Ã s estruturas do prÃ³prio `PHP`.

#### InstruÃ§Ã£o if

VocÃª pode usar as diretivas `@if`, `@elseif`, `@else` e `@endif` para construir instruÃ§Ãµes `if`. Essas diretivas funcionam exatamente como suas instruÃ§Ãµes correspondentes no `PHP`:

```blade
@if (count($records) === 1)
    I have one record!
@elseif (count($records)> 1)
    I have multiple records!
@else
    I don't have any records!
@endif
```

Por conveniÃªncia, o `Blade` tambÃ©m fornece a instruÃ§Ã£o `@unless`:

```blade
@unless (is_signed_in())
    You are not signed in.
@endunless
```

AlÃ©m das diretivas condicionais jÃ¡ discutidas, as diretivas `@isset` e `@empty` tambÃ©m podem ser usadas como atalhos para suas funÃ§Ãµes correspondentes no `PHP`:

```blade
@isset($records)
    // $records has been defined but not empty
@endisset

@empty($records)
    // $records is empty...
@endempty
```

#### Diretivas de bloco

VocÃª pode usar o comando `@hasSection` para determinar se o bloco contÃ©m conteÃºdo:

```blade
@hasSection('navigation')
    <div class="pull-right">
        @yield('navigation')
    </div>

    <div class="clearfix"></div>
@endif
```

VocÃª pode usar o comando `@sectionMissing` para determinar se o bloco nÃ£o tem conteÃºdo:

```blade
@sectionMissing('navigation')
    <div class="pull-right">
        @include('default-navigation')
    </div>
@endif
```

#### Diretivas de ambiente

VocÃª pode usar o comando `@production` para determinar se a aplicaÃ§Ã£o estÃ¡ em ambiente de produÃ§Ã£o:

```blade
@production
    // Production environment specific content...
@endproduction
```

Ou vocÃª pode usar o comando `@env` para determinar se a aplicaÃ§Ã£o estÃ¡ rodando em um ambiente especÃ­fico:

```blade
@env('staging')
    // The application is running in the "staging" environment...
@endenv

@env(['staging','production'])
    // The application is running in a "staging" environment or a production environment...
@endenv
```

#### InstruÃ§Ã£o switch

VocÃª pode usar as diretivas `@switch`, `@case`, `@break`, `@default` e `@endswitch` para construir uma instruÃ§Ã£o `switch`:

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

#### LaÃ§os

AlÃ©m de condicionais, o `Blade` tambÃ©m oferece diretivas com as mesmas funÃ§Ãµes das estruturas de laÃ§o do `PHP`. Da mesma forma, essas diretivas sÃ£o consistentes com a sintaxe correspondente do `PHP`:

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

> Ao usar laÃ§os, vocÃª pode usar variÃ¡veis de loop para obter informaÃ§Ãµes Ãºteis sobre o laÃ§o, por exemplo, se estÃ¡ na primeira iteraÃ§Ã£o ou na Ãºltima iteraÃ§Ã£o.

Ao usar um laÃ§o, vocÃª pode encerrar o laÃ§o ou pular a iteraÃ§Ã£o atual:

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

VocÃª pode declarar uma condiÃ§Ã£o em uma Ãºnica linha da diretiva:

```blade
@foreach ($users as $user)
    @continue($user->type == 1)

    <li>{{ $user->name }}</li>

    @break($user->number == 5)
@endforeach
```

#### VariÃ¡vel de loop

Ao iterar, a variÃ¡vel `$loop` pode ser usada dentro do laÃ§o. Ela fornece uma forma de acessar informaÃ§Ãµes como o Ã­ndice atual do loop e se esta iteraÃ§Ã£o Ã© a primeira ou a Ãºltima:

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

Se vocÃª estiver em um loop aninhado, pode acessar o loop pai usando a propriedade `parent` da variÃ¡vel `$loop`:

```blade
@foreach ($users as $user)
    @foreach ($user->posts as $post)
        @if ($loop->parent->first)
            This is first iteration of the parent loop.
        @endif
    @endforeach
@endforeach
```

A variÃ¡vel `$loop` tambÃ©m contÃ©m vÃ¡rios atributos Ãºteis:

| Propriedades | ObservaÃ§Ãµes |
|:--:|:--:|
| `$loop->index` | O Ã­ndice da iteraÃ§Ã£o atual (a partir de 0). |
| `$loop->iteration` | O nÃºmero da iteraÃ§Ã£o atual do loop (a partir de 1). |
| `$loop->remaining` | O nÃºmero de iteraÃ§Ãµes restantes do loop. |
| `$loop->count` | A quantidade de elementos do array a ser iterado. |
| `$loop->first` | Se a iteraÃ§Ã£o atual Ã© a primeira iteraÃ§Ã£o do loop. |
| `$loop->last` | Se a iteraÃ§Ã£o atual Ã© a Ãºltima iteraÃ§Ã£o do loop. |
| `$loop->even` | Se o nÃºmero da iteraÃ§Ã£o atual do loop Ã© par. |
| `$loop->odd` | Se o nÃºmero da iteraÃ§Ã£o atual do loop Ã© Ã­mpar. |
| `$loop->depth` | A profundidade de aninhamento do loop atual. |
| `$loop->parent` | O loop pai em um loop aninhado. |

#### ComentÃ¡rio

O `Blade` tambÃ©m permite definir comentÃ¡rios na view. Mas, diferente de comentÃ¡rios `HTML`, comentÃ¡rios do `Blade` nÃ£o serÃ£o incluÃ­dos no `HTML` retornado pela aplicaÃ§Ã£o:

```blade
{{-- This comment will not be present in the rendered HTML --}}
```

#### PHP

Em muitos casos, Ã© Ãºtil embutir cÃ³digo `PHP` na sua view. VocÃª pode usar a diretiva `@php` do `Blade` no template para executar um bloco de cÃ³digo `PHP` nativo:

```blade
@php
    //
@endphp
```

> Embora o Blade forneÃ§a esse recurso, o uso frequente pode fazer com que muita lÃ³gica fique embutida nos seus templates.

#### Diretiva @once

A diretiva `@once` permite definir uma parte do conteÃºdo do template que serÃ¡ avaliada apenas uma vez a cada ciclo de renderizaÃ§Ã£o.
Essa diretiva Ã© muito Ãºtil no contexto de usar `stack` para fazer push de um cÃ³digo `JavaScript` especÃ­fico para o head da pÃ¡gina.
Por exemplo, se vocÃª quiser renderizar um `component` especÃ­fico dentro de um loop, talvez vocÃª queira fazer push do `JavaScript` para o head apenas na primeira vez em que o componente for renderizado:

```blade
@once
    @push('scripts')
        <script>
            // Your custom JavaScript code
        </script>
    @endpush
@endonce
```

### Componentes e slots

O papel de componentes e slots Ã© semelhante ao de Section e Layout. No entanto, algumas pessoas podem achar componentes e slots mais convenientes de usar. O Hyperf suporta duas formas de escrever componentes: componentes baseados em classe e componentes anÃ´nimos.

Podemos definir um componente de classe criando uma classe que herda `\Hyperf\ViewEngine\Component\Component::class`. A seguir, serÃ¡ mostrado como usar o componente criando um componente `Alert` simples.

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

#### Registrar componentes manualmente

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

Ou no `ConfigProvider` do pacote de extensÃ£o

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

ApÃ³s registrar o componente, vocÃª poderÃ¡ usÃ¡-lo por meio de aliases de tags HTML:

```html
<x-alert/>
<x-package-alert/>
```

#### Exibir componentes

VocÃª pode usar tags de componente do Blade em qualquer template Blade para exibir componentes. A tag de componente do Blade comeÃ§a com `x-`, seguida do nome do componente.

```html
<x-alert/>
<x-package-alert/>
```

#### Passagem de parÃ¢metros para componentes

Você pode usar atributos HTML para passar dados para o componente Blade. Valores comuns podem ser passados por atributos HTML simples, enquanto expressões e variáveis PHP devem ser passadas por atributos prefixados com `:`:

```html
<x-alert type="error" :message="$message"/>
```

!> Nota: vocÃª pode definir os dados exigidos pelo componente no construtor da classe do componente. Todas as propriedades pÃºblicas na classe do componente serÃ£o automaticamente passadas para a view do componente. NÃ£o Ã© necessÃ¡rio passÃ¡-las pelo mÃ©todo `render` da classe do componente. Ao renderizar um componente, vocÃª pode obter o conteÃºdo das propriedades pÃºblicas da classe do componente pelo nome da variÃ¡vel.

#### MÃ©todos do componente

AlÃ©m de obter as propriedades pÃºblicas da classe do componente, vocÃª tambÃ©m pode executar quaisquer mÃ©todos pÃºblicos da classe do componente na view do componente. Por exemplo, um componente tem um mÃ©todo `isSelected`:

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

VocÃª pode executar o mÃ©todo chamando a variÃ¡vel com o mesmo nome do mÃ©todo:

```html
    <option {{ $isSelected($value)?'selected="selected"':'' }} value="{{ $value }}">
        {{ $label }}
    </option>
```

#### DependÃªncias adicionais

Se o seu componente precisar depender de outras classes, vocÃª deve listÃ¡-las antes de todos os atributos de dados do componente; elas serÃ£o automaticamente injetadas pelo container:
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

#### Gerenciar atributos

Vimos como passar atributos de dados para componentes. PorÃ©m, Ã s vezes precisamos especificar outros atributos HTML (como `class`) que nÃ£o fazem parte dos dados exigidos pelo componente. Nesse caso, queremos repassar esses atributos para o elemento raiz do template do componente. Por exemplo, queremos renderizar um componente de alerta assim:

```html
    <x-alert type="error" :message="$message" class="mt-4"/>
```

Todos os atributos que nÃ£o fazem parte do construtor do componente serÃ£o automaticamente adicionados Ã  "bolsa de atributos" (property bag) do componente. Esse conjunto de atributos serÃ¡ passado para a view do componente pela variÃ¡vel `$attributes`. Ao imprimir essa variÃ¡vel, todos os atributos podem ser renderizados no componente:

```html
    <div {{ $attributes }}>
        <!-- Component content -->
    </div>
```

#### Obter atributos

VocÃª pode usar o mÃ©todo `get()` para obter o valor de um atributo especÃ­fico. Esse mÃ©todo aceita o nome do atributo como primeiro parÃ¢metro (o segundo parÃ¢metro Ã© o valor padrÃ£o) e retorna seu valor.

```html
    <div class="{{ $attributes->get("class", "default") }}">
        <!-- Component content -->
    </div>
```

#### Verificar atributos

VocÃª pode usar o mÃ©todo `has()` para verificar a existÃªncia de um atributo especÃ­fico. Esse mÃ©todo aceita o nome do atributo como parÃ¢metro e retorna um valor booleano.

```html
    @if($attributes->has("class"))
        <div class="{{ $attributes->get("class") }}">
            <!-- Component content -->
        </div>
    @endif
```

#### Mesclar atributos

Em algum momento, você pode precisar definir o valor padrão de um atributo ou incorporar outros valores em certos atributos do componente. Para isso, você pode usar o método `merge` da property bag:

```html
    <div {{ $attributes->merge(['class' =>'alert alert-'.$type]) }}>
        {{ $message }}
    </div>
```

Suponha que usamos este componente como mostrado abaixo:

```html
    <x-alert type="error" :message="$message" class="mb-4"/>
```

O HTML final renderizado do componente ficarÃ¡ assim:

```html
    <div class="alert alert-error mb-4">
        <!-- The content of the $message variable -->
    </div>
```

Por padrÃ£o, apenas os atributos `class` serÃ£o mesclados; outros atributos serÃ£o sobrescritos diretamente. As seguintes situaÃ§Ãµes ocorrerÃ£o:

```blade
// definition
<div {{ $attributes->merge(['class' =>'alert alert-'.$type,'other-attr' =>'foo']) }}>{{ $message }}</div>
// use
<x-alert type="error" :message="$message" class="mb-4" other-attr="bar"/>
// present
<div class="alert alert-error mb-4" other-attr="bar"><!-- The content of the $message variable --></div>
```

Como no caso acima, se vocÃª precisar mesclar o atributo `other-attr`, pode usar a seguinte forma, adicionando o segundo parÃ¢metro `true` ao mÃ©todo `merge()`:

```blade
// definition
<div {{ $attributes->merge(['class' =>'alert alert-'.$type,'other-attr' =>'foo'], true) }}>{{ $message }}</ div>
// use
<x-alert type="error" :message="$message" class="mb-4" other-attr="bar"/>
// present
<div class="alert alert-error mb-4" other-attr="foo bar"><!-- The content of the $message variable --></div>
```

#### Slot

Geralmente, vocÃª precisa passar conteÃºdo adicional para o componente por meio de `slots`. Suponha que o componente de alerta que criamos tenha a seguinte marcaÃ§Ã£o:

```html
    <!-- /storage/view/components/alert.blade.php -->

    <div class="alert alert-danger">
        {{ $slot }}
    </div>
```

Podemos passar conteúdo para `slots` injetando conteúdo no componente:

```html
    <x-alert>
        <strong>Whoops!</strong> Something went wrong!
    </x-alert>
```

Às vezes, um componente pode precisar colocar vários slots diferentes em posições diferentes. Vamos modificar o componente de alerta para permitir a injeção de `title`.

```html
    <!-- /storage/view/components/alert.blade.php -->

    <span class="alert-title">{{ $title }}</span>

    <div class="alert alert-danger">
        {{ $slot }}
    </div>
```

Você pode usar a tag `x-slot` para definir o conteúdo de um slot nomeado. Outro conteúdo que não estiver dentro da tag `x-slot` será passado para o componente na variável `$slot`:

```html
    <x-alert>
        <x-slot name="title">
            Server Error
        </x-slot>

        <strong>Whoops!</strong> Something went wrong!
    </x-alert>
```

#### Componentes inline

Para componentes pequenos, gerenciar classes de componentes e templates de view pode ser trabalhoso. Por isso, você pode retornar o conteúdo do componente a partir do método `render`:

```php
    public function render()
    {
        return <<<'blade'
            <div class="alert alert-danger">
                {{ $slot }}
            </div>blade;
    }
```

#### Componentes anônimos

Assim como componentes inline, componentes anônimos fornecem um mecanismo para gerenciar componentes por meio de um único arquivo. Porém, um componente anônimo usa um único arquivo de view sem classes associadas. Para definir um componente anônimo, basta colocar o template Blade no diretório `/storage/view/components`.
Por exemplo, suponha que você defina um componente em `/storage/view/components/alert.blade.php`:

```html
    <x-alert/>
```

Se o componente estiver em um subdiretório de `components`, você pode usar o caractere `.` para especificar o caminho. Por exemplo, se o componente estiver definido em `/storage/view/components/inputs/button.blade.php`, você pode renderizá-lo assim:

```html
    <x-inputs.button/>
```

#### Dados e atributos de componentes anônimos

Como componentes anônimos não têm classes associadas, pode ser útil distinguir quais dados devem ser passados para o componente como variáveis e quais atributos devem ser armazenados na [bolsa de atributos](#gerenciar-atributos).

Você pode usar a diretiva @props no nível superior do template Blade do componente para especificar quais atributos devem ser usados como variáveis de dados. Todos os outros atributos do componente serão fornecidos na forma de uma property bag. Se você quiser definir um valor padrão para uma variável de dados, pode usar o nome do atributo como chave do array e o valor padrão como valor do array:

```blade
    <!-- /storage/view/components/alert.blade.php -->

    @props(['type' =>'info','message'])

    <div {{ $attributes->merge(['class' =>'alert alert-'.$type]) }}>
        {{ $message }}
    </div>
```

#### Componentes dinâmicos

Às vezes, você pode precisar renderizar um componente, mas não sabe qual deve renderizar antes da execução. Nesse caso, você pode usar o componente embutido `dynamic-component` para renderizar um componente com base em valores ou variáveis:

```html
    <x-dynamic-component :component="$componentName" class="mt-4" />
```

#### Carregamento automático de componentes

Por padrão, componentes em `App\\View\\Component\\` e `components.` são registrados automaticamente. Você também pode modificar essa configuração pelo arquivo de configuração:

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

## Namespace de views

Ao definir um namespace de views, você pode usar facilmente arquivos de view no seu pacote de extensão. Você só precisa adicionar uma linha de configuração no `ConfigProvider`:

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

Depois de instalar o pacote de extensão, você pode sobrescrever a view do pacote definindo um arquivo de view com o mesmo caminho em `/storage/view/vendor/package-name` no projeto.

## Middlewares opcionais

- Hyperf\ViewEngine\Http\Middleware\ShareErrorsFromSession::class

Compartilha automaticamente `errors` da `session` com a view, dependendo do componente `hyperf/session`

- Hyperf\ViewEngine\Http\Middleware\ValidationExceptionHandle::class

Captura automaticamente exceções de `validation` e as adiciona à `session`, dependendo dos componentes `hyperf/session` e `hyperf/validation`

## Outros comandos

Instalação automática das configurações relacionadas aos componentes `view-engine`, `translation` e `validation`

```
php bin/hyperf.php view:publish
```
