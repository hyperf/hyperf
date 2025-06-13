# 视图引擎

> 基于 Laravel blade 模板引擎改写, 支持原始 blade 模板引擎的语法.

```bash
composer require hyperf/view-engine
```

## 生成配置

```bash
php bin/hyperf.php vendor:publish hyperf/view-engine
```

默认配置如下

> 本组件推荐使用 SYNC 的渲染模式，可以有效减少进程间通信的损耗

```php
return [
    'engine' => Hyperf\ViewEngine\HyperfViewEngine::class,
    'mode' => Hyperf\View\Mode::SYNC,
    'config' => [
        'view_path' => BASE_PATH . '/storage/view/',
        'cache_path' => BASE_PATH . '/runtime/view/',
    ],

    # 自定义组件注册
    'components' => [
        // 'alert' => \App\View\Components\Alert::class
    ],

    # 视图命名空间 (主要用于扩展包中)
    'namespaces' => [
        // 'admin' => BASE_PATH . '/storage/view/vendor/admin',
    ],
];
```

## 使用

> 本使用教程大量借鉴于 [LearnKu](https://learnku.com)，十分感谢 LearnKu 对 PHP 社区做出的贡献。

### 简介

`Blade` 是 `Laravel` 提供的一个简单而又强大的模板引擎。和其他流行的 `PHP` 模板引擎不同，`Blade` 并不限制你在视图中使用原生 `PHP` 代码。
所有 `Blade` 视图文件都将被编译成原生的 `PHP` 代码并缓存起来，除非它被修改，否则不会重新编译，这就意味着 `Blade` 基本上不会给你的应用增加任何负担。
`Blade` 视图文件使用 `.blade.php` 作为文件扩展名，默认被存放在 `storage/view` 目录。

### 模板继承

#### 定义布局

首先，我们来研究一个「主」页面布局。因为大多数 `web` 应用会在不同的页面中使用相同的布局方式，因此可以很方便地定义单个 `Blade` 布局视图：

```blade
<!-- Stored in storage/view/layouts/app.blade.php -->

<html>
    <head>
        <title>App Name - @yield('title')</title>
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

你可以看见，这段程序里包含常见的 `HTML`。但请注意 `@section` 和 `@yield` 和指令。如同 `section` 的意思，一个片段，`@section` 指令定义了片段的内容，而 `@yield` 指令则用来显示片段的内容。

现在，我们已经定义好了这个应用程序的布局，接下来，我们定义一个继承此布局的子页面。

#### 布局继承

在定义一个子视图时，使用 `Blade` 的 `@extends` 指令指定子视图要「继承」的视图。扩展自 `Blade` 布局的视图可以使用 `@section` 指令向布局片段注入内容。
就如前面的示例中所示，这些片段的内容将由布局中的 `@yield` 指令控制显示：

```blade
<!-- Stored in storage/view/child.blade.php -->

@extends('layouts.app')

@section('title', 'Page Title')

@section('sidebar')
    @parent

    <p>This is appended to the master sidebar.</p>
@endsection

@section('content')
    <p>This is my body content.</p>
@endsection
```

在这个示例中，`sidebar` 片段利用 `@parent` 指令向布局的 `sidebar` 追加（而非覆盖）内容。 在渲染视图时，`@parent` 指令将被布局中的内容替换。

> 和上一个示例相反，这里的 sidebar 片段使用 @endsection 代替 @show 来结尾。 @endsection 指令仅定义了一个片段， @show 则在定义的同时 立即 yield 这个片段。

`@yield` 指令还接受一个默认值作为第二个参数。如果被「yield」的片段未定义，则该默认值被渲染：

```blade
@yield('content', 'Hyperf')
```

`Blade` 视图可以用 `Hyperf\ViewEngine\view` 辅助函数返回：

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

### 显示数据

你可以把变量置于花括号中以在视图中显示数据。 例如，给定下方的路由：

```php
use Hyperf\HttpServer\Router\Router;
use function Hyperf\ViewEngine\view;

Router::get('greeting', function () {
    return view('welcome', ['name' => 'Samantha']);
});
```

您可以像如下这样显示 `name` 变量的内容：

```blade
Hello, {{ $name }}.
```

> Blade 的 {{ }} 语句将被 PHP 的 htmlspecialchars 函数自动转义以防范 XSS 攻击。

不仅仅可以显示传递给视图的变量的内容，您亦可输出任何 `PHP` 函数的结果。事实上，您可以在 `Blade` 模板的回显语句放置任何 `PHP` 代码：

```blade
The current UNIX timestamp is {{ time() }}.
```

#### 显示非转义字符

默认情况下，`Blade {{ }}` 语句将被 `PHP` 的 `htmlspecialchars` 函数自动转义以防范 `XSS` 攻击。如果您不想您的数据被转义，那么您可使用如下的语法：

```blade
Hello, {!! $name !!}.
```

> 在应用中显示用户提供的数据时请格外小心，请尽可能的使用转义和双引号语法来防范 XSS 攻击。

#### 渲染 JSON

有时，为了初始化一个 `JavaScript` 变量，您可能会向视图传递一个数组并将其渲染成 `JSON`。例如：

```
<script>
    var app = <?php echo json_encode($array); ?>;
</script>
```

当然，您亦可使用 `@json` `Blade` 指令来代替手动调用 `json_encode` 方法。`@json` 指令的参数和 `PHP` 的 `json_encode` 函数一致：

```html
<script>
    var app = @json($array);

    var app = @json($array, JSON_PRETTY_PRINT);
</script>
```

> 使用 @json 指令时，您应该只渲染已经存在的变量为 JSON。Blade 模板是基于正则表达式的，如果尝试将一个复杂表达式传递给 @json 指令可能会导致无法预测的错误。

#### HTML 实体编码

默认情况下，`Blade` 将会对 `HTML` 实体进行双重编码。如果您想要禁用此举，您可以监听 `BootApplication` 事件，并调用 `Blade::withoutDoubleEncoding` 方法：

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
    private ContainerInterface $container;

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

#### Blade & JavaScript 框架

由于许多 `JavaScript` 框架也使用「花括号」来标识将显示在浏览器中的表达式，因此，您可以使用 `@` 符号来表示 `Blade` 渲染引擎应当保持不变。例如：

```blade
<h1>Laravel</h1>

Hello, @{{ name }}.
```

在这个例子中，`@` 符号将被 `Blade` 移除；当然，`Blade` 将不会修改 `{{ name }}` 表达式，取而代之的是 `JavaScript` 模板来对其进行渲染。
`@` 符号也用于转义 `Blade` 指令：

```
{{-- Blade --}}
@@json()

<!-- HTML 输出 -->
@json()
```

如果您在模板中显示很大一部分 `JavaScript` 变量，您可以将 `HTML` 嵌入到 `@verbatim` 指令中，这样，您就不需要在每一个 `Blade` 回显语句前添加 `@` 符号：

```blade
@verbatim
    <div class="container">
        Hello, {{ name }}.
    </div>
@endverbatim
```

### 流程控制

除了模板继承和显示数据以外，`Blade` 还为常见的 `PHP` 控制结构提供了便捷的快捷方式，例如条件语句和循环。这些快捷方式为 `PHP` 控制结构提供了一个非常清晰、简洁的书写方式，同时，还与 `PHP` 中的控制结构保持了相似的语法特性。

#### If 语句

您可以使用 `@if`，`@elseif`，`@else` 和 `@endif` 指令构造 `if` 语句。这些指令功能与它们所对应的 `PHP` 语句完全一致：

```blade
@if (count($records) === 1)
    I have one record!
@elseif (count($records) > 1)
    I have multiple records!
@else
    I don't have any records!
@endif
```

为了方便，`Blade` 还提供了一个 `@unless` 指令：

```blade
@unless (is_signed_in())
    You are not signed in.
@endunless
```

除了已经讨论过了的条件指令外，`@isset` 和 `@empty` 指令亦可作为它们所对应的 `PHP` 函数的快捷方式：

```blade
@isset($records)
    // $records 已经定义但不为空
@endisset

@empty($records)
    // $records 为空……
@endempty
```

#### 区块指令

您可以使用 `@hasSection` 指令来判断区块是否有内容：

```blade
@hasSection('navigation')
    <div class="pull-right">
        @yield('navigation')
    </div>

    <div class="clearfix"></div>
@endif
```

您可以使用 `@sectionMissing` 指令来判断区块是否没有内容：

```blade
@sectionMissing('navigation')
    <div class="pull-right">
        @include('default-navigation')
    </div>
@endif
```

#### 环境指令

您可以使用 `@production` 指令来判断应用是否处于生产环境：

```blade
@production
    // 生产环境特定内容……
@endproduction
```

或者，您可以使用 `@env` 指令来判断应用是否运行于指定的环境：

```blade
@env('staging')
    // 应用运行于「staging」环境……
@endenv

@env(['staging', 'production'])
    // 应用运行于 「staging」环境或生产环境……
@endenv
```

#### Switch 语句

您可使用 `@switch`，`@case`，`@break`，`@default` 和 `@endswitch` 语句来构造 `Switch` 语句：

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

#### 循环

除了条件语句，`Blade` 还提供了与 `PHP` 循环结构功能相同的指令。同样，这些语句的功能和它们所对应的 `PHP` 语法一致：

```blade
@for ($i = 0; $i < 10; $i++)
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

> 循环时，您可以使用 循环变量 去获取有关循环的有价值的信息，例如，您处于循环的第一个迭代亦或是处于最后一个迭代。

在使用循环的时候，您可以终止循环或跳过当前迭代：

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

您可以在指令的单独一行中声明一个条件语句：

```blade
@foreach ($users as $user)
    @continue($user->type == 1)

    <li>{{ $user->name }}</li>

    @break($user->number == 5)
@endforeach
```

#### Loop 变量

循环时，循环内部可以使用 `$loop` 变量。该变量提供了访问一些诸如当前的循环索引和此次迭代是首次或是末次这样的信息的方式：

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

如果您在嵌套循环中，您可以使用循环的 `$loop` 的变量的 `parent` 属性访问父级循环：

```blade
@foreach ($users as $user)
    @foreach ($user->posts as $post)
        @if ($loop->parent->first)
            This is first iteration of the parent loop.
        @endif
    @endforeach
@endforeach
```

`$loop` 变量还包含各种各样有用的属性：

| 属性 | 备注 |
|:--:|:--:|
| `$loop->index` | 当前迭代的索引（从 0 开始）。|
| `$loop->iteration` | 当前循环的迭代次数（从 1 开始）。|
| `$loop->remaining` | 循环剩余的迭代次数。|
| `$loop->count` | 被迭代的数组的元素个数。|
| `$loop->first` | 当前迭代是否是循环的首次迭代。|
| `$loop->last` | 当前迭代是否是循环的末次迭代。|
| `$loop->even` | 当前循环的迭代次数是否是偶数。|
| `$loop->odd` | 当前循环的迭代次数是否是奇数。|
| `$loop->depth` | 当前循环的嵌套深度。|
| `$loop->parent` | 嵌套循环中的父级循环。|

#### 注释

`Blade` 也允许您在视图中定义注释。但是和 `HTML` 注释不同， `Blade` 注释不会被包含在应用返回的 `HTML` 中：

```blade
{{-- This comment will not be present in the rendered HTML --}}
```

#### PHP

在许多情况下，嵌入 `PHP` 代码到您的视图中是很有用的。您可以在模板中使用 `Blade` 的 `@php` 指令执行原生的 `PHP` 代码块：

```blade
@php
    //
@endphp
```

> 尽管 Blade 提供了这个功能，频繁使用它可能使得您的模板中嵌入过多的逻辑。

#### @once 指令

`@once` 指令允许您定义模板的一部分内容，这部分内容在每一个渲染周期中只会被计算一次。
该指令在使用 `堆栈` 推送一段特定的 `JavaScript` 代码到页面的头部环境下是很有用的。
例如，如果您想要在循环中渲染一个特定的 `组件`，您可能希望仅在组件渲染的首次推送 `JavaScript` 代码到头部：

```blade
@once
    @push('scripts')
        <script>
            // 您自定义的 JavaScript 代码
        </script>
    @endpush
@endonce
```

### 组件及插槽

组件和插槽的作用与片段（Section）和布局（Layout）类似。不过，有些人可能认为组件和插槽使用起来更加方便。Hyperf 支持两种编写组件的方法：基于类的组件和匿名组件。

我们可以通过创建一个继承 `\Hyperf\ViewEngine\Component\Component::class` 类的 class 来定义一个类组件。以下将通过创建一个简单的 `Alert` 组件来向你说明如何使用组件。

> app/View/Component/Alert.php

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
        return view('components.alert');
    }
}
```

> storage/view/components/alert.blade.php
```html
<div class="alert alert-{{ $type }}">
    {{ $message }}
</div>
```

#### 手动注册组件

在 `config/autoload/view.php` 中

```php
<?php
return [
    // ...
    'components' => [
        'alert' => \App\View\Component\Alert::class,
    ],
];
```

或者在扩展包中的 `ConfigProvider` 中

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

注册组件后，你将可以通过 HTML 标签别名来使用它：

```html
<x-alert/>
<x-package-alert/>
```

#### 显示组件

你可以在任一 Blade 模板中使用 Blade 组件标签来显示组件。Blade 组件标签以 `x-` 开头，后面接上组件的名称。

```html
<x-alert/>
<x-package-alert/>
```

#### 组件传参

你可以使用 HTML 属性将数据传递给 Blade 组件。普通的值可以通过简单的 HTML 属性传递，而 PHP 表达式及变量应当通过以 `:` 为前缀的属性传递：

```html
<x-alert type="error" :message="$message"/>
```

!> 注意：你可以在组件类的构造函数中定义组件所需的数据。组件类中的所有公共属性都将自动传递给组件视图。不必通过组件类的 `render` 方法传递。渲染组件时，可以通过变量名称来获取组件类公共属性的内容。

#### 组件方法

除了可获取组件类的公共属性外，还可以在组件视图中执行组件类上的任何公共方法。例如，某组件具有一个 `isSelected` 方法：

```php
    /**
     * 判断给定选项是否为当前选项
     *
     * @param  string  $option
     * @return bool
     */
    public function isSelected($option)
    {
        return $option === $this->selected;
    }
```

你可以通过调用与方法名称相同的变量来执行该方法：

```html
    <option {{ $isSelected($value) ? 'selected="selected"' : '' }} value="{{ $value }}">
        {{ $label }}
    </option>
```

#### 附加依赖项

如果你的组件需要依赖其他的类，则应当在组件所有数据属性之前列出它们，它们将会被容器自动注入：
```php
    use App\AlertCreator;
    /**
     * 创建组件实例
     *
     * @param  \App\AlertCreator  $creator
     * @param  string  $type
     * @param  string  $message
     * @return void
     */
    public function __construct(AlertCreator $creator, $type, $message)
    {
        $this->creator = $creator;
        $this->type = $type;
        $this->message = $message;
    }
```

#### 管理属性

我们已经了解了如何将数据属性传递给组件。然而，有时候我们可能需要指定其他的 HTML 属性（如 `class`），这些属性不是组件所需要的数据。这种情况下，我们将会想要将这些属性向下传递到组件模板的根元素。例如，我们要渲染一个 `alert` 组件，如下所示：

```html
    <x-alert type="error" :message="$message" class="mt-4"/>
```

所有不属于组件构造函数的属性都将自动添加到组件的「属性包」中。该属性包将会通过 `$attributes` 变量传递给组件视图。通过输出此变量，即可在组件中呈现所有属性：

```html
    <div {{ $attributes }}>
        <!-- 组件内容 -->
    </div>
```

#### 获取属性

您可以使用 `get()` 方法获取特定的属性值。此方法接受属性名称作为第一个参数(第二个参数为默认值)，并将返回其值。

```html
    <div class="{{ $attributes->get("class", "default") }}">
        <!-- 组件内容 -->
    </div>
```

#### 检测属性

您可以使用 `has()` 方法获取特定的属性值。此方法接受属性名称作为参数，并将返回布尔值。

```html
    @if($attributes->has("class"))
        <div class="{{ $attributes->get("class") }}">
            <!-- 组件内容 -->
        </div>
    @endif
```

#### 合并属性

某些时候，你可能需要指定属性的默认值，或将其他值合并到组件的某些属性中。为此，你可以使用属性包的 `merge` 方法：

```html
    <div {{ $attributes->merge(['class' => 'alert alert-'.$type]) }}>
        {{ $message }}
    </div>
```

假设我们如下方所示使用该组件：

```html
    <x-alert type="error" :message="$message" class="mb-4"/>
```

最终呈现的组件 HTML 将如下所示：

```html
    <div class="alert alert-error mb-4">
        <!-- $message 变量的内容 -->
    </div>
```

默认情况下，只会合并 `class` 属性，其他属性将会直接进行覆盖，会出现如下情况：

```blade
// 定义
<div {{ $attributes->merge(['class' => 'alert alert-'.$type, 'other-attr' => 'foo']) }}>{{ $message }}</div>
// 使用
<x-alert type="error" :message="$message" class="mb-4" other-attr="bar"/>
// 呈现
<div class="alert alert-error mb-4" other-attr="bar"><!-- $message 变量的内容 --></div>
```

如上述情况，需要将 `other-attr` 属性也合并的话，可以使用以下方式，在 `merge()` 方法中添加第二个参数 `true` ：

```blade
// 定义
<div {{ $attributes->merge(['class' => 'alert alert-'.$type, 'other-attr' => 'foo'], true) }}>{{ $message }}</div>
// 使用
<x-alert type="error" :message="$message" class="mb-4" other-attr="bar"/>
// 呈现
<div class="alert alert-error mb-4" other-attr="foo bar"><!-- $message 变量的内容 --></div>
```

#### 插槽

通常，你需要通过 `slots` 向组件传递附加内容。 假设我们创建的 `alert` 组件具有以下标记：

```html
    <!-- /storage/view/components/alert.blade.php -->

    <div class="alert alert-danger">
        {{ $slot }}
    </div>
```

我门可以通过向组件注入内容的方式，将内容传递到 `slots` ：

```html
    <x-alert>
        <strong>Whoops!</strong> Something went wrong!
    </x-alert>
```

有时候一个组件可能需要在它内部的不同位置放置多个不同的插槽。我们来修改一下 alert 组件，使其允许注入 `title` 。

```html
    <!-- /storage/view/components/alert.blade.php -->

    <span class="alert-title">{{ $title }}</span>

    <div class="alert alert-danger">
        {{ $slot }}
    </div>
```

你可以使用 `x-slot` 标签来定义一个命名插槽的内容。而不在 `x-slot` 标签中的其它内容都将传递给 `$slot` 变量中的组件：

```html
    <x-alert>
        <x-slot name="title">
            Server Error
        </x-slot>

        <strong>Whoops!</strong> Something went wrong!
    </x-alert>
```

#### 内联组件

对于小型组件而言，管理组件类和组件视图模板可能会很麻烦。因此，您可以从 `render` 方法中返回组件的内容：

```php
    public function render()
    {
        return <<<'blade'
            <div class="alert alert-danger">
                {{ $slot }}
            </div>
        blade;
    }
```

#### 匿名组件

与行内组件相同，匿名组件提供了一个通过单个文件管理组件的机制。然而，匿名组件使用的是一个没有关联类的单一视图文件。要定义一个匿名组件，您只需将 Blade 模板置于 `/storage/view/components` 目录下。
例如，假设您在 `/storage/view/components/alert.blade.php` 中定义了一个组件：

```html
    <x-alert/>
```

如果组件在 `components` 目录的子目录中，您可以使用 `.` 字符来指定其路径。例如，假设组件被定义在 `/storage/view/components/inputs/button.blade.php` 中，您可以像这样渲染它：

```html
    <x-inputs.button/>
```

#### 匿名组件数据及属性

由于匿名组件没有任何关联类，您可能想要区分哪些数据应该被作为变量传递给组件，而哪些属性应该被存放于 [属性包](#管理属性) 中。

您可以在组件的 Blade 模板的顶层使用 `@props` 指令来指定哪些属性应该作为数据变量。组件中的其他属性都将通过属性包的形式提供。如果您想要为某个数据变量指定一个默认值，您可以将属性名作为数组键，默认值作为数组值来实现：

```blade
    <!-- /storage/view/components/alert.blade.php -->

    @props(['type' => 'info', 'message'])

    <div {{ $attributes->merge(['class' => 'alert alert-'.$type]) }}>
        {{ $message }}
    </div>
```

#### 动态组件

有时，您可能需要渲染一个组件，但在运行前不知道要渲染哪一个。这种情况下，您可以使用内置的 `dynamic-component` 组件来渲染一个基于值或变量的组件：

```html
    <x-dynamic-component :component="$componentName" class="mt-4" />
```

#### 组件自动加载

默认情况下，`App\View\Component\` 及 `components.` 下的组件会自动注册。你也可以通过配置文件修改这个配置：

> config/autoload/view.php

```php
return [
    // ...
    'autoload' => [
        'classes' => ['App\\Other\\Component\\', 'App\\Another\\Component\\'],
        'components' => ['package::components.', 'components.'],
    ],
];
```

## 视图空间

通过定义视图空间，可以方便的在你的扩展包中使用视图文件，只需要在 `ConfigProvider` 中添加一行配置即可：

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
                    'package-name' => __DIR__ . '/../views',
                ],
            ],
        ];
    }
}
```

在安装扩展包之后，可以通过在项目的 `/storage/view/vendor/package-name` 中定义相同路径的视图文件，来覆盖扩展包中的视图。

## 可选中间件

- Hyperf\ViewEngine\Http\Middleware\ShareErrorsFromSession::class

自动将 `session` 中的 `errors` 共享给视图，依赖 `hyperf/session` 组件

- Hyperf\ViewEngine\Http\Middleware\ValidationExceptionHandle::class

自动捕捉 `validation` 中的异常加入到 `session` 中，依赖 `hyperf/session` 和 `hyperf/validation` 组件

## 其他命令

自动安装 `view-engine`、`translation` 和 `validation` 组件相关配置

```
php bin/hyperf.php view:publish
```
