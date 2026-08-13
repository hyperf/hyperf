# View Engine

> Based on the Laravel blade template engine modification, it supports the syntax of the original blade template engine.

```bash
composer require hyperf/view-engine
```

## Generate Configuration

```bash
php bin/hyperf.php vendor:publish hyperf/view-engine
```

The default configuration is as follows:

> This component recommends using the SYNC rendering mode, which can effectively reduce the loss of inter-process communication.

```php
return [
    'engine' => Hyperf\ViewEngine\HyperfViewEngine::class,
    'mode' => Hyperf\View\Mode::SYNC,
    'config' => [
        'view_path' => BASE_PATH . '/storage/view/',
        'cache_path' => BASE_PATH . '/runtime/view/',
    ],

    # Custom component registration
    'components' => [
        // 'alert' => \App\View\Components\Alert::class
    ],
    
    # View namespace (mainly used in extension packages)
    'namespaces' => [
        // 'admin' => BASE_PATH . '/storage/view/vendor/admin',
    ],
];
```

## Usage

> This tutorial borrows heavily from [LearnKu](https://learnku.com). Many thanks to LearnKu for its contribution to the PHP community.

### Introduction

`Blade` is a simple yet powerful template engine provided by `Laravel`. Unlike other popular `PHP` template engines, `Blade` does not restrict you from using native `PHP` code in views.
All `Blade` view files will be compiled into native `PHP` code and cached. Unless they are modified, they will not be recompiled, which means `Blade` basically adds no burden to your application.
`Blade` view files use `.blade.php` as the file extension and are stored in the `storage/view` directory by default.

### Template Inheritance

#### Define Layout

First, let's study a "master" page layout. Since most `web` applications use the same layout on different pages, it is convenient to define a single `Blade` layout view:

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

As you can see, this code contains common `HTML`. But note the `@section` and `@yield` directives. As the meaning of `section` implies, an `@section` directive defines the content of a segment, while the `@yield` directive is used to display the content of that segment.
Now that we have defined the layout for this application, let's define a child page that inherits from this layout.

#### Layout Inheritance

When defining a child view, use the `Blade` `@extends` directive to specify the view that the child view should "inherit". A view that extends a `Blade` layout can inject content into the layout segments using the `@section` directive.
As shown in the previous example, the content of these segments will be controlled for display by the `@yield` directive in the layout:

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

In this example, the `sidebar` segment uses the `@parent` directive to append (rather than overwrite) content to the layout's `sidebar`. When the view is rendered, the `@parent` directive will be replaced by the content in the layout.

> Contrary to the previous example, the sidebar segment here ends with @endsection instead of @show. The @endsection directive only defines a segment, while @show immediately yields this segment while defining it.

The `@yield` directive also accepts a default value as the second argument. If the "yielded" segment is not defined, the default value is rendered:

```blade
@yield('content', 'Hyperf')
```

`Blade` views can be returned by the `Hyperf\ViewEngine\view` helper function:

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

### Displaying Data

You can place variables within curly braces to display data in the view. For example, given the following route:

```php
use Hyperf\HttpServer\Router\Router;
use function Hyperf\ViewEngine\view;

Router::get('greeting', function () {
    return view('welcome', ['name' => 'Samantha']);
});
```

You can display the content of the `name` variable like this:

```blade
Hello, {{ $name }}.
```

> Blade's {{ }} statements are automatically escaped by PHP's htmlspecialchars function to prevent XSS attacks.

Not only can you display the content of variables passed to the view, but you can also output the results of any `PHP` function. In fact, you can place any `PHP` code within `Blade` template echo statements:

```blade
The current UNIX timestamp is {{ time() }}.
```

#### Displaying Unescaped Characters

By default, `Blade {{ }}` statements are automatically escaped by `PHP`'s `htmlspecialchars` function to prevent `XSS` attacks. If you do not want your data to be escaped, you can use the following syntax:

```blade
Hello, {!! $name !!}.
```

> Be extremely careful when displaying user-supplied data in your application. Please use escaping and double-quote syntax whenever possible to prevent XSS attacks.

#### Rendering JSON

Sometimes, to initialize a `JavaScript` variable, you might pass an array to the view and render it as `JSON`. For example:

```
<script>
    var app = <?php echo json_encode($array); ?>;
</script>
```

Of course, you can also use the `@json` `Blade` directive instead of manually calling the `json_encode` method. The arguments for the `@json` directive are the same as those for `PHP`'s `json_encode` function:

```html
<script>
    var app = @json($array);

    var app = @json($array, JSON_PRETTY_PRINT);
</script>
```

> When using the @json directive, you should only render variables that already exist as JSON. Blade templates are regex-based, and attempting to pass a complex expression to the @json directive might lead to unpredictable errors.

#### HTML Entity Encoding

By default, `Blade` will double-encode `HTML` entities. If you want to disable this, you can listen for the `BootApplication` event and call the `Blade::withoutDoubleEncoding` method:

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

#### Blade & JavaScript Frameworks

Since many `JavaScript` frameworks also use "curly braces" to identify expressions that will be displayed in the browser, you can use the `@` symbol to indicate to the `Blade` rendering engine that it should remain unchanged. For example:

```blade
<h1>Laravel</h1>

Hello, @{{ name }}.
```

In this example, the `@` symbol will be removed by `Blade`; of course, `Blade` will not modify the `{{ name }}` expression, and the `JavaScript` template will render it instead.
The `@` symbol is also used to escape `Blade` directives:

```
{{-- Blade --}}
@@json()

<!-- HTML output -->
@json()
```

If you display a large portion of `JavaScript` variables in your template, you can embed the `HTML` into an `@verbatim` directive, so that you do not need to add the `@` symbol before every `Blade` echo statement:

```blade
@verbatim
    <div class="container">
        Hello, {{ name }}.
    </div>
@endverbatim
```

### Control Structures

In addition to template inheritance and displaying data, `Blade` provides convenient shortcuts for common `PHP` control structures, such as conditional statements and loops. These shortcuts provide a very clear and concise way to write `PHP` control structures while maintaining a similar syntax to control structures in `PHP`.

#### If Statements

You can construct `if` statements using the `@if`, `@elseif`, `@else`, and `@endif` directives. These directives function exactly the same as their corresponding `PHP` statements:

```blade
@if (count($records) === 1)
    I have one record!
@elseif (count($records) > 1)
    I have multiple records!
@else
    I don't have any records!
@endif
```

For convenience, `Blade` also provides an `@unless` directive:

```blade
@unless (is_signed_in())
    You are not signed in.
@endunless
```

In addition to the conditional directives already discussed, the `@isset` and `@empty` directives can also be used as shortcuts for their corresponding `PHP` functions:

```blade
@isset($records)
    // $records is defined and not empty
@endisset

@empty($records)
    // $records is empty...
@endempty
```

#### Block Directives

You can use the `@hasSection` directive to check if a block has content:

```blade
@hasSection('navigation')
    <div class="pull-right">
        @yield('navigation')
    </div>

    <div class="clearfix"></div>
@endif
```

You can use the `@sectionMissing` directive to check if a block has no content:

```blade
@sectionMissing('navigation')
    <div class="pull-right">
        @include('default-navigation')
    </div>
@endif
```

#### Environment Directives

You can use the `@production` directive to check if the application is in the production environment:

```blade
@production
    // Production environment specific content...
@endproduction
```

Alternatively, you can use the `@env` directive to check if the application is running in a specified environment:

```blade
@env('staging')
    // Application is running in the "staging" environment...
@endenv

@env(['staging', 'production'])
    // Application is running in the "staging" environment or production environment...
@endenv
```

#### Switch Statements

You can construct `Switch` statements using the `@switch`, `@case`, `@break`, `@default`, and `@endswitch` statements:

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

#### Loops

In addition to conditional statements, `Blade` provides directives that have the same functionality as `PHP` loop structures. Similarly, these statements function the same as their corresponding `PHP` syntax:

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

> When looping, you can use loop variables to obtain valuable information about the loop, for example, whether you are in the first iteration of the loop or the last iteration.

When using loops, you can terminate the loop or skip the current iteration:

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

You can declare a conditional statement on a single line of the directive:

```blade
@foreach ($users as $user)
    @continue($user->type == 1)

    <li>{{ $user->name }}</li>

    @break($user->number == 5)
@endforeach
```

#### Loop Variable

When looping, the `$loop` variable can be used inside the loop. This variable provides a way to access information such as the current loop index and whether this iteration is the first or last:

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

If you are in a nested loop, you can use the `parent` property of the loop's `$loop` variable to access the parent loop:

```blade
@foreach ($users as $user)
    @foreach ($user->posts as $post)
        @if ($loop->parent->first)
            This is first iteration of the parent loop.
        @endif
    @endforeach
@endforeach
```

The `$loop` variable also contains various useful properties:

| Property | Note |
|:--:|:--:|
| `$loop->index` | The index of the current iteration (starting from 0). |
| `$loop->iteration` | The number of current loop iterations (starting from 1). |
| `$loop->remaining` | The number of remaining iterations in the loop. |
| `$loop->count` | The number of elements in the array being iterated. |
| `$loop->first` | Whether the current iteration is the first iteration of the loop. |
| `$loop->last` | Whether the current iteration is the last iteration of the loop. |
| `$loop->even` | Whether the number of current loop iterations is even. |
| `$loop->odd` | Whether the number of current loop iterations is odd. |
| `$loop->depth` | The nesting depth of the current loop. |
| `$loop->parent` | The parent loop in a nested loop. |

#### Comments

`Blade` also allows you to define comments in views. However, unlike `HTML` comments, `Blade` comments will not be included in the `HTML` returned by the application:

```blade
{{-- This comment will not be present in the rendered HTML --}}
```

#### PHP

In many cases, embedding `PHP` code into your views is useful. You can execute native `PHP` code blocks in templates using `Blade`'s `@php` directive:

```blade
@php
    //
@endphp
```

> Although Blade provides this functionality, using it frequently may result in embedding too much logic in your templates.

#### @once Directive

The `@once` directive allows you to define a portion of the template that will be computed only once in each rendering cycle.
This directive is very useful when pushing a specific piece of `JavaScript` code to the head of the page using `stacks`.
For example, if you want to render a specific `component` in a loop, you might want to push `JavaScript` code to the head only when the component is rendered for the first time:

```blade
@once
    @push('scripts')
        <script>
            // Your custom JavaScript code
        </script>
    @endpush
@endonce
```

### Components and Slots

Components and slots have a similar role to Sections and Layouts. However, some may find components and slots more convenient to use. Hyperf supports two methods for writing components: class-based components and anonymous components.

We can define a class component by creating a class that extends the `\Hyperf\ViewEngine\Component\Component::class` class. The following will explain how to use components by creating a simple `Alert` component.

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

#### Manually Registering Components

In `config/autoload/view.php`:

```php
<?php
return [
    // ...
    'components' => [
        'alert' => \App\View\Component\Alert::class,
    ],
];
```

Or in the `ConfigProvider` of an extension package:

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

After registering a component, you can use it via its HTML tag alias:

```html
<x-alert/>
<x-package-alert/>
```

#### Displaying Components

You can use Blade component tags in any Blade template to display components. Blade component tags start with `x-`, followed by the name of the component.

```html
<x-alert/>
<x-package-alert/>
```

#### Passing Data to Components

You can pass data to Blade components using HTML attributes. Ordinary values can be passed via simple HTML attributes, while PHP expressions and variables should be passed via attributes prefixed with `:`.

```html
<x-alert type="error" :message="$message"/>
```

!> Note: You can define the data required by the component in the constructor of the component class. All public properties of the component class will be automatically passed to the component view. It is not necessary to pass them via the component class's `render` method. When rendering a component, you can get the content of the public properties of the component class via variable name.

#### Component Methods

In addition to accessing public properties of the component class, you can also execute any public method on the component class in the component view. For example, a component has an `isSelected` method:

```php
    /**
     * Determine if the given option is currently selected
     *
     * @param  string  $option
     * @return bool
     */
    public function isSelected($option)
    {
        return $option === $this->selected;
    }
```

You can execute this method by calling a variable with the same name as the method:

```html
    <option {{ $isSelected($value) ? 'selected="selected"' : '' }} value="{{ $value }}">
        {{ $label }}
    </option>
```

#### Additional Dependencies

If your component needs to depend on other classes, list them before all data attributes of the component, and they will be automatically injected by the container:

```php
    use App\AlertCreator;
    /**
     * Create a component instance
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

#### Managing Attributes

We have already learned how to pass data attributes to components. However, sometimes we may need to specify other HTML attributes (like `class`), which are not data required by the component. In this case, we will want to pass these attributes down to the root element of the component template. For example, if we want to render an `alert` component, as follows:

```html
    <x-alert type="error" :message="$message" class="mt-4"/>
```

All attributes that do not belong to the component constructor will be automatically added to the component's "attribute bag". This attribute bag will be passed to the component view via the `$attributes` variable. By outputting this variable, you can present all attributes in the component:

```html
    <div {{ $attributes }}>
        <!-- Component content -->
    </div>
```

#### Getting Attributes

You can use the `get()` method to get specific attribute values. This method accepts the attribute name as the first argument (the second argument is the default value) and returns its value.

```html
    <div class="{{ $attributes->get("class", "default") }}">
        <!-- Component content -->
    </div>
```

#### Checking Attributes

You can use the `has()` method to get a specific attribute value. This method accepts the attribute name as an argument and returns a boolean value.

```html
    @if($attributes->has("class"))
        <div class="{{ $attributes->get("class") }}">
            <!-- Component content -->
        </div>
    @endif
```

#### Merging Attributes

Sometimes, you may need to specify default values for attributes, or merge other values into certain attributes of the component. To do this, you can use the `merge` method of the attribute bag:

```html
    <div {{ $attributes->merge(['class' => 'alert alert-'.$type]) }}>
        {{ $message }}
    </div>
```

Suppose we use the component as shown below:

```html
    <x-alert type="error" :message="$message" class="mb-4"/>
```

The finally presented component HTML will be as follows:

```html
    <div class="alert alert-error mb-4">
        <!-- Content of $message variable -->
    </div>
```

By default, only the `class` attribute will be merged; other attributes will be directly overwritten, resulting in the following situation:

```blade
// Define
<div {{ $attributes->merge(['class' => 'alert alert-'.$type, 'other-attr' => 'foo']) }}>{{ $message }}</div>
// Use
<x-alert type="error" :message="$message" class="mb-4" other-attr="bar"/>
// Render
<div class="alert alert-error mb-4" other-attr="bar"><!-- Content of $message variable --></div>
```

As in the case above, if you need to merge the `other-attr` attribute as well, you can use the following method by adding a second argument `true` to the `merge()` method:

```blade
// Define
<div {{ $attributes->merge(['class' => 'alert alert-'.$type, 'other-attr' => 'foo'], true) }}>{{ $message }}</div>
// Use
<x-alert type="error" :message="$message" class="mb-4" other-attr="bar"/>
// Render
<div class="alert alert-error mb-4" other-attr="foo bar"><!-- Content of $message variable --></div>
```

#### Slots

Typically, you need to pass additional content to a component via `slots`. Suppose the `alert` component we created has the following markup:

```html
    <!-- /storage/view/components/alert.blade.php -->

    <div class="alert alert-danger">
        {{ $slot }}
    </div>
```

We can pass content to the `slots` by injecting content into the component:

```html
    <x-alert>
        <strong>Whoops!</strong> Something went wrong!
    </x-alert>
```

Sometimes a component might need to place multiple different slots in different positions within it. Let's modify the `alert` component to allow injecting a `title`.

```html
    <!-- /storage/view/components/alert.blade.php -->

    <span class="alert-title">{{ $title }}</span>

    <div class="alert alert-danger">
        {{ $slot }}
    </div>
```

You can use the `x-slot` tag to define content for a named slot. Other content not within the `x-slot` tag will be passed to the `$slot` variable of the component:

```html
    <x-alert>
        <x-slot name="title">
            Server Error
        </x-slot>

        <strong>Whoops!</strong> Something went wrong!
    </x-alert>
```

#### Inline Components

For small components, managing both the component class and component view template can be cumbersome. Therefore, you can return the content of the component from the `render` method:

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

#### Anonymous Components

Similar to inline components, anonymous components provide a mechanism for managing components through a single file. However, anonymous components use a single view file that is not associated with a class. To define an anonymous component, you simply need to place the Blade template in the `/storage/view/components` directory.
For example, suppose you defined a component in `/storage/view/components/alert.blade.php`:

```html
    <x-alert/>
```

If the component is in a subdirectory of the `components` directory, you can use the `.` character to specify its path. For example, suppose the component is defined in `/storage/view/components/inputs/button.blade.php`. You can render it like this:

```html
    <x-inputs.button/>
```

#### Anonymous Component Data and Attributes

Since anonymous components do not have any associated classes, you might want to distinguish which data should be passed to the component as variables and which attributes should be stored in the [attribute bag](#managing-attributes).

You can use the `@props` directive at the top level of the component's Blade template to specify which attributes should be treated as data variables. Other attributes in the component will be provided in the form of an attribute bag. If you want to specify a default value for a data variable, you can use the attribute name as the array key and the default value as the array value:

```blade
    <!-- /storage/view/components/alert.blade.php -->

    @props(['type' => 'info', 'message'])

    <div {{ $attributes->merge(['class' => 'alert alert-'.$type]) }}>
        {{ $message }}
    </div>
```

#### Dynamic Components

Sometimes, you might need to render a component but do not know which one to render before running. In this case, you can use the built-in `dynamic-component` component to render a component based on a value or variable:

```html
    <x-dynamic-component :component="$componentName" class="mt-4" />
```

#### Automatic Component Loading

By default, components under `App\View\Component\` and `components.` are automatically registered. You can also modify this configuration via the configuration file:

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

## View Namespace

By defining view namespaces, it is convenient to use view files in your extension packages. You just need to add a line of configuration in the `ConfigProvider`:

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

After installing the extension package, you can overwrite the view files in the extension package by defining view files with the same path in the project's `/storage/view/vendor/package-name` directory.

## Optional Middleware

- Hyperf\ViewEngine\Http\Middleware\ShareErrorsFromSession::class

Automatically share `errors` from `session` with the view, relies on the `hyperf/session` component

- Hyperf\ViewEngine\Http\Middleware\ValidationExceptionHandle::class

Automatically capture exceptions in `validation` and add them to `session`, relies on `hyperf/session` and `hyperf/validation` components

## Other Commands

Automatically install configuration related to `view-engine`, `translation`, and `validation` components

```
php bin/hyperf.php view:publish
```
