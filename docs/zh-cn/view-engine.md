# 视图引擎

> 基于 laravel blade 模板引擎改写, 支持原始 blade 模板引擎的语法.

```
composer require hyperf/view-engine
```

## 生成配置

```
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
<!-- Stored in resources/views/child.blade.php -->

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

`Blade` 视图可以用 `Hyperf\\ViewEngine\\view` 辅助函数返回：

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Annotation\AutoController;
use function Hyperf\ViewEngine\view;

/**
 * @AutoController(prefix="view")
 */
class ViewController extends Controller
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

/**
 * @Listener
 */
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

#### Blade & JavaScript 框架

由于许多 `JavaScript` 框架也使用「花括号」来标识将显示在浏览器中的表达式，因此，您可以使用 `@` 符号来表示 `Blade` 渲染引擎应当保持不便。例如：

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
