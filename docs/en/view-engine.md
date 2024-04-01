# View engine

> Rewritten based on the laravel blade template engine, supporting the syntax of the original blade template engine.

```bash
composer require hyperf/view-engine
```

## Generate configuration

```bash
php bin/hyperf.php vendor:publish hyperf/view-engine
```

The default configuration is as follows

> This component recommends using the SYNC rendering mode, which can effectively reduce the loss of inter-process communication

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

## Use

> This tutorial is borrowed heavily from [LearnKu](https://learnku.com), and I am very grateful to LearnKu for its contribution to the PHP community.

### Introduction

`Blade` is a simple and powerful template engine provided by `Laravel`. Unlike other popular `PHP` template engines, `Blade` does not restrict you from using native `PHP` code in views.
All `Blade` view files will be compiled into native `PHP` code and cached, unless it is modified, otherwise it will not be recompiled, which means that `Blade` basically does not add any burden to your application .
The `Blade` view file uses `.blade.php` as the file extension and is stored in the `storage/view` directory by default.

### Template inheritance

#### Define the layout

First, let's study a "main" page layout. Because most `web` applications will use the same layout on different pages, it is easy to define a single `Blade` layout view:

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

As you can see, this program contains common `HTML`. But please pay attention to `@section` and `@yield` and instructions. Just like the meaning of `section`, for a section, the `@section` directive defines the content of the section, and the `@yield` directive is used to display the content of the section.

Now that we have defined the layout of this application, next, we define a subpage that inherits this layout.

#### Layout inheritance

When defining a subview, use the `@extends` directive of `Blade` to specify the view that the subview should "inherit". Views that extend from the `Blade` layout can use the `@section` directive to inject content into the layout section.
As shown in the previous example, the content of these fragments will be controlled and displayed by the `@yield` directive in the layout:

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

In this example, the `sidebar` fragment uses the `@parent` directive to append (not overwrite) content to the `sidebar` of the layout. When rendering the view, the `@parent` directive will be replaced by the content in the layout.

> Contrary to the previous example, the sidebar fragment here ends with @endsection instead of @show. The @endsection directive defines only one section, while @show immediately yields this section while defining it.

The `@yield` command also accepts a default value as the second parameter. If the "yield" fragment is not defined, the default value is rendered:

```blade
@yield('content','Hyperf')
```

The `Blade` view can be returned by the `Hyperf\ViewEngine\view` helper function:

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

### Displaying data

You can put variables in curly braces to display data in the view. For example, given the following route:

```php
use Hyperf\HttpServer\Router\Router;
use function Hyperf\ViewEngine\view;

Router::get('greeting', function () {
    return view('welcome', ['name' =>'Samantha']);
});
```

You can display the contents of the `name` variable as follows:

```blade
Hello, {{ $name }}.
```

> Blade's {{ }} statement will be automatically escaped by PHP's htmlspecialchars function to prevent XSS attacks.

Not only can you display the contents of the variables passed to the view, you can also output the result of any `PHP` function. In fact, you can put any PHP code in the echo statement of the Blade template:

```blade
The current UNIX timestamp is {{ time() }}.
```

#### Display non-escaped characters

By default, `Blade {{ }}` statements will be automatically escaped by `PHP`'s `htmlspecialchars` function to prevent `XSS` attacks. If you don't want your data to be escaped, you can use the following syntax:

```blade
Hello, {!! $name !!}.
```

> Please be very careful when displaying user-supplied data in the app. Use escaping and double-quotation syntax as much as possible to prevent XSS attacks.

#### Render JSON

Sometimes, in order to initialize a `JavaScript` variable, you may pass an array to the view and render it as `JSON`. E.g:

```
<script>
    var app = <?php echo json_encode($array); ?>;
</script>
```

Of course, you can also use `@json` `Blade` command instead of manually calling the `json_encode` method. The parameters of the `@json` instruction are the same as the `json_encode` function of `PHP`:

```html
<script>
    var app = @json($array);

    var app = @json($array, JSON_PRETTY_PRINT);
</script>
```

> When using the @json directive, you should only render existing variables as JSON. The Blade template is based on regular expressions. Trying to pass a complex expression to the @json directive may cause unpredictable errors.

#### HTML entity encoding

By default, `Blade` will double-encode `HTML` entities. If you want to disable this, you can listen to the `BootApplication` event and call the `Blade::withoutDoubleEncoding` method:

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

#### Blade & JavaScript framework

Since many JavaScript frameworks also use "curly braces" to identify expressions that will be displayed in the browser, you can use the @ symbol to indicate that the Blade rendering engine should be inconvenient. E.g:

```blade
<h1>Laravel</h1>

Hello, @{{ name }}.
```

In this example, the `@` symbol will be removed by `Blade`; of course, `Blade` will not modify the `{{ name }}` expression, instead `JavaScript` template to render it.
The `@` symbol is also used to escape the `Blade` instruction:

```
{{-- Blade --}}
@@json()

<!-- HTML output -->
@json()
```

If you display a large part of the `JavaScript` variables in the template, you can embed `HTML` in the `@verbatim` directive, so that you don’t need to add the `@` symbol before every `Blade` echo statement :

```blade
@verbatim
    <div class="container">
        Hello, {{ name }}.
    </div>
@endverbatim
```

### Process Control

In addition to template inheritance and display data, `Blade` also provides convenient shortcuts for common `PHP` control structures, such as conditional statements and loops. These shortcuts provide a very clear and concise way of writing the `PHP` control structure. At the same time, it also maintains the similar grammatical characteristics to the control structure in `PHP`.

#### If statement

You can use `@if`, `@elseif`, `@else` and `@endif` directives to construct `if` statements. The functions of these commands are exactly the same as their corresponding `PHP` statements:

```blade
@if (count($records) === 1)
    I have one record!
@elseif (count($records)> 1)
    I have multiple records!
@else
    I don't have any records!
@endif
```

For convenience, `Blade` also provides a `@unless` instruction:

```blade
@unless (is_signed_in())
    You are not signed in.
@endunless
```

In addition to the conditional instructions already discussed, the `@isset` and `@empty` instructions can also be used as shortcuts to their corresponding `PHP` functions:

```blade
@isset($records)
    // $records has been defined but not empty
@endisset

@empty($records)
    // $records is empty...
@endempty
```

#### Block instructions

You can use the `@hasSection` command to determine whether the block contains content:

```blade
@hasSection('navigation')
    <div class="pull-right">
        @yield('navigation')
    </div>

    <div class="clearfix"></div>
@endif
```

You can use the `@sectionMissing` command to determine whether the block has no content:

```blade
@sectionMissing('navigation')
    <div class="pull-right">
        @include('default-navigation')
    </div>
@endif
```

#### Environmental Directives

You can use the `@production` command to determine whether the application is in a production environment:

```blade
@production
    // Production environment specific content...
@endproduction
```

Or, you can use the `@env` command to determine whether the application is running in a specified environment:

```blade
@env('staging')
    // The application is running in the "staging" environment...
@endenv

@env(['staging','production'])
    // The application is running in a "staging" environment or a production environment...
@endenv
```

#### Switch statement

You can use `@switch`, `@case`, `@break`, `@default` and `@endswitch` statements to construct a `Switch` statement:

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

In addition to conditional statements, `Blade` also provides instructions with the same functions as the loop structure of `PHP`. Similarly, the functions of these statements are consistent with their corresponding `PHP` syntax:

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

> When looping, you can use loop variables to get valuable information about the loop, for example, you are in the first iteration or the last iteration of the loop.

When using a loop, you can terminate the loop or skip the current iteration:

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

You can declare a conditional statement on a single line of the instruction:

```blade
@foreach ($users as $user)
    @continue($user->type == 1)

    <li>{{ $user->name }}</li>

    @break($user->number == 5)
@endforeach
```

#### Loop variable

When looping, the `$loop` variable can be used inside the loop. This variable provides a way to access some information such as the current loop index and whether this iteration is the first or last time:

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

If you are in a nested loop, you can access the parent loop using the `parent` property of the loop's `$loop` variable:

```blade
@foreach ($users as $user)
    @foreach ($user->posts as $post)
        @if ($loop->parent->first)
            This is first iteration of the parent loop.
        @endif
    @endforeach
@endforeach
```

The `$loop` variable also contains various useful attributes:

| Properties | Remarks |
|:--:|:--:|
| `$loop->index` | The index of the current iteration (starting from 0). |
| `$loop->iteration` | The number of iterations of the current loop (starting from 1). |
| `$loop->remaining` | The number of remaining iterations of the loop. |
| `$loop->count` | The number of elements in the array to be iterated. |
| `$loop->first` | Whether the current iteration is the first iteration of the loop. |
| `$loop->last` | Whether the current iteration is the last iteration of the loop. |
| `$loop->even` | Whether the number of iterations of the current loop is even. |
| `$loop->odd` | Whether the number of iterations of the current loop is odd. |
| `$loop->depth` | The nesting depth of the current loop. |
| `$loop->parent` | The parent loop in the nested loop. |

#### Comment

`Blade` also allows you to define comments in the view. But unlike `HTML` comments, `Blade` comments will not be included in the `HTML` returned by the application:

```blade
{{-- This comment will not be present in the rendered HTML --}}
```

#### PHP

In many cases, it is useful to embed `PHP` code in your view. You can use the `@php` instruction of `Blade` in the template to execute the native `PHP` code block:

```blade
@php
    //
@endphp
```

> Although Blade provides this feature, frequent use of it may cause too much logic to be embedded in your templates.

#### @once directive

The `@once` directive allows you to define part of the template content, which will only be calculated once in each rendering cycle.
This instruction is very useful in the context of using the `stack` to push a specific `JavaScript` code to the head of the page.
For example, if you want to render a specific `component` in a loop, you may want to push the `JavaScript` code to the head only the first time the component is rendered:

```blade
@once
    @push('scripts')
        <script>
            // Your custom JavaScript code
        </script>
    @endpush
@endonce
```

### Components and slots

The role of components and slots is similar to that of Section and Layout. However, some people may think that components and slots are more convenient to use. Hyperf supports two methods of writing components: class-based components and anonymous components.

We can define a class component by creating a class that inherits the `\Hyperf\ViewEngine\Component\Component::class` class. The following will show you how to use the component by creating a simple `Alert` component.

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

#### Manually register components

In `config/autoload/view.php`

```php
<?php
return [
    // ...
    'components' => [
        'alert' => \App\View\Component\Alert::class,
    ],
];
```

Or in the `ConfigProvider` in the extension package

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

After registering the component, you will be able to use it through HTML tag aliases:

```html
<x-alert/>
<x-package-alert/>
```

#### Display components

You can use Blade component tags in any Blade template to display components. The Blade component label starts with `x-`, followed by the name of the component.

```html
<x-alert/>
<x-package-alert/>
```

#### Component parameter transfer

You can use HTML attributes to pass data to the Blade component. Ordinary values ​​can be passed through simple HTML attributes, while PHP expressions and variables should be passed through attributes prefixed with `:`:

```html
<x-alert type="error" :message="$message"/>
```

!> Note: You can define the data required by the component in the constructor of the component class. All public properties in the component class will be automatically passed to the component view. It does not have to be passed through the `render` method of the component class. When rendering a component, you can get the content of the public properties of the component class through the variable name.

#### Component method

In addition to obtaining the public properties of the component class, you can also execute any public methods on the component class in the component view. For example, a component has an `isSelected` method:

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

You can execute the method by calling the variable with the same name as the method:

```html
    <option {{ $isSelected($value)?'selected="selected"':'' }} value="{{ $value }}">
        {{ $label }}
    </option>
```

#### Additional dependencies

If your component needs to depend on other classes, you should list them before all the data attributes of the component, and they will be automatically injected by the container:
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

#### Manage properties

We have seen how to pass data attributes to components. However, sometimes we may need to specify other HTML attributes (such as `class`), which are not the data required by the component. In this case, we will want to pass these attributes down to the root element of the component template. For example, we want to render an alert component as follows:

```html
    <x-alert type="error" :message="$message" class="mt-4"/>
```

All properties that are not part of the component's constructor will be automatically added to the component's "property bag". The attribute package will be passed to the component view via the `$attributes` variable. By outputting this variable, all properties can be rendered in the component:

```html
    <div {{ $attributes }}>
        <!-- Component content -->
    </div>
```

#### Get attributes

You can use the `get()` method to get a specific attribute value. This method accepts the attribute name as the first parameter (the second parameter is the default value) and returns its value.

```html
    <div class="{{ $attributes->get("class", "default") }}">
        <!-- Component content -->
    </div>
```

#### Detecting attributes

You can use the `has()` method to get a specific attribute value. This method accepts the attribute name as a parameter and will return a boolean value.

```html
    @if($attributes->has("class"))
        <div class="{{ $attributes->get("class") }}">
            <!-- Component content -->
        </div>
    @endif
```

#### Merging attributes

At some point, you may need to specify the default value of an attribute, or incorporate other values ​​into certain attributes of the component. For this, you can use the `merge` method of the property bag:

```html
    <div {{ $attributes->merge(['class' =>'alert alert-'.$type]) }}>
        {{ $message }}
    </div>
```

Suppose we use this component as shown below:

```html
    <x-alert type="error" :message="$message" class="mb-4"/>
```

The final rendered component HTML will look like this:

```html
    <div class="alert alert-error mb-4">
        <!-- The content of the $message variable -->
    </div>
```

By default, only the `class` attributes will be merged, and other attributes will be directly overwritten. The following situations will occur:

```blade
// definition
<div {{ $attributes->merge(['class' =>'alert alert-'.$type,'other-attr' =>'foo']) }}>{{ $message }}</div>
// use
<x-alert type="error" :message="$message" class="mb-4" other-attr="bar"/>
// present
<div class="alert alert-error mb-4" other-attr="bar"><!-- The content of the $message variable --></div>
```

As in the above case, if you need to merge the `other-attr` attributes, you can use the following method to add the second parameter `true` to the `merge()` method:

```blade
// definition
<div {{ $attributes->merge(['class' =>'alert alert-'.$type,'other-attr' =>'foo'], true) }}>{{ $message }}</ div>
// use
<x-alert type="error" :message="$message" class="mb-4" other-attr="bar"/>
// present
<div class="alert alert-error mb-4" other-attr="foo bar"><!-- The content of the $message variable --></div>
```

#### Slot

Usually, you need to pass additional content to the component through `slots`. Assume that the alert component we created has the following markup:

```html
    <!-- /storage/view/components/alert.blade.php -->

    <div class="alert alert-danger">
        {{ $slot }}
    </div>
```

We can pass content to `slots` by injecting content into the component:

```html
    <x-alert>
        <strong>Whoops!</strong> Something went wrong!
    </x-alert>
```

Sometimes a component may need to place multiple different slots in different positions within it. Let's modify the alert component to allow the injection of `title`.

```html
    <!-- /storage/view/components/alert.blade.php -->

    <span class="alert-title">{{ $title }}</span>

    <div class="alert alert-danger">
        {{ $slot }}
    </div>
```

You can use the `x-slot` tag to define the content of a named slot. Other content not in the `x-slot` tag will be passed to the components in the `$slot` variable:

```html
    <x-alert>
        <x-slot name="title">
            Server Error
        </x-slot>

        <strong>Whoops!</strong> Something went wrong!
    </x-alert>
```

#### Inline components

For small components, managing component classes and component view templates can be troublesome. Therefore, you can return the content of the component from the `render` method:

```php
    public function render()
    {
        return <<<'blade'
            <div class="alert alert-danger">
                {{ $slot }}
            </div>blade;
    }
```

#### Anonymous components

Like inline components, anonymous components provide a mechanism for managing components through a single file. However, the anonymous component uses a single view file with no associated classes. To define an anonymous component, you only need to place the Blade template in the `/storage/view/components` directory.
For example, suppose you define a component in `/storage/view/components/alert.blade.php`:

```html
    <x-alert/>
```

If the component is in a subdirectory of the `components` directory, you can use the `.` character to specify its path. For example, if the component is defined in `/storage/view/components/inputs/button.blade.php`, you can render it like this:

```html
    <x-inputs.button/>
```

#### Anonymous component data and attributes

Since anonymous components do not have any associated classes, you may want to distinguish which data should be passed to the component as variables and which properties should be stored in [property package](#Manage Attribute).

You can use the @props directive at the top level of the Blade template of the component to specify which properties should be used as data variables. All other properties in the component will be provided in the form of a property bag. If you want to specify a default value for a certain data variable, you can use the attribute name as the array key and the default value as the array value:

```blade
    <!-- /storage/view/components/alert.blade.php -->

    @props(['type' =>'info','message'])

    <div {{ $attributes->merge(['class' =>'alert alert-'.$type]) }}>
        {{ $message }}
    </div>
```

#### Dynamic components

Sometimes, you may need to render a component, but do not know which one to render before running. In this case, you can use the built-in `dynamic-component` component to render a component based on values ​​or variables:

```html
    <x-dynamic-component :component="$componentName" class="mt-4" />
```

#### Automatic component loading

By default, components under `App\View\Component\` and `components.` are automatically registered. You can also modify this configuration through the configuration file:

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

## View space

By defining the view space, you can easily use the view file in your extension package. You only need to add a line of configuration in `ConfigProvider`:

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

After installing the extension package, you can override the view in the extension package by defining a view file with the same path in the project's `/storage/view/vendor/package-name`.

## Optional middleware

- Hyperf\ViewEngine\Http\Middleware\ShareErrorsFromSession::class

Automatically share the `errors` in the `session` to the view, relying on the `hyperf/session` component

- Hyperf\ViewEngine\Http\Middleware\ValidationExceptionHandle::class

Automatically catch exceptions in `validation` and add them to `session`, relying on `hyperf/session` and `hyperf/validation` components

## Other commands

Automatic installation of `view-engine`, `translation` and `validation` component related configuration

```
php bin/hyperf.php view:publish
```
