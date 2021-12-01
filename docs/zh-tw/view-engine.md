# 檢視引擎

> 基於 Laravel blade 模板引擎改寫, 支援原始 blade 模板引擎的語法.

```bash
composer require hyperf/view-engine
```

## 生成配置

```bash
php bin/hyperf.php vendor:publish hyperf/view-engine
```

預設配置如下

> 本元件推薦使用 SYNC 的渲染模式，可以有效減少程序間通訊的損耗

```php
return [
    'engine' => Hyperf\ViewEngine\HyperfViewEngine::class,
    'mode' => Hyperf\View\Mode::SYNC,
    'config' => [
        'view_path' => BASE_PATH . '/storage/view/',
        'cache_path' => BASE_PATH . '/runtime/view/',
    ],

    # 自定義元件註冊
    'components' => [
        // 'alert' => \App\View\Components\Alert::class
    ],

    # 檢視名稱空間 (主要用於擴充套件包中)
    'namespaces' => [
        // 'admin' => BASE_PATH . '/storage/view/vendor/admin',
    ],
];
```

## 使用

> 本使用教程大量借鑑於 [LearnKu](https://learnku.com)，十分感謝 LearnKu 對 PHP 社群做出的貢獻。

### 簡介

`Blade` 是 `Laravel` 提供的一個簡單而又強大的模板引擎。和其他流行的 `PHP` 模板引擎不同，`Blade` 並不限制你在檢視中使用原生 `PHP` 程式碼。
所有 `Blade` 檢視檔案都將被編譯成原生的 `PHP` 程式碼並快取起來，除非它被修改，否則不會重新編譯，這就意味著 `Blade` 基本上不會給你的應用增加任何負擔。
`Blade` 檢視檔案使用 `.blade.php` 作為副檔名，預設被存放在 `storage/view` 目錄。

### 模板繼承

#### 定義佈局

首先，我們來研究一個「主」頁面佈局。因為大多數 `web` 應用會在不同的頁面中使用相同的佈局方式，因此可以很方便地定義單個 `Blade` 佈局檢視：

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

你可以看見，這段程式裡包含常見的 `HTML`。但請注意 `@section` 和 `@yield` 和指令。如同 `section` 的意思，一個片段，`@section` 指令定義了片段的內容，而 `@yield` 指令則用來顯示片段的內容。

現在，我們已經定義好了這個應用程式的佈局，接下來，我們定義一個繼承此佈局的子頁面。

#### 佈局繼承

在定義一個子檢視時，使用 `Blade` 的 `@extends` 指令指定子檢視要「繼承」的檢視。擴充套件自 `Blade` 佈局的檢視可以使用 `@section` 指令向佈局片段注入內容。
就如前面的示例中所示，這些片段的內容將由佈局中的 `@yield` 指令控制顯示：

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

在這個示例中，`sidebar` 片段利用 `@parent` 指令向佈局的 `sidebar` 追加（而非覆蓋）內容。 在渲染檢視時，`@parent` 指令將被佈局中的內容替換。

> 和上一個示例相反，這裡的 sidebar 片段使用 @endsection 代替 @show 來結尾。 @endsection 指令僅定義了一個片段， @show 則在定義的同時 立即 yield 這個片段。

`@yield` 指令還接受一個預設值作為第二個引數。如果被「yield」的片段未定義，則該預設值被渲染：

```blade
@yield('content', 'Hyperf')
```

`Blade` 檢視可以用 `Hyperf\ViewEngine\view` 輔助函式返回：

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

### 顯示資料

你可以把變數置於花括號中以在檢視中顯示資料。 例如，給定下方的路由：

```php
use Hyperf\HttpServer\Router\Router;
use function Hyperf\ViewEngine\view;

Router::get('greeting', function () {
    return view('welcome', ['name' => 'Samantha']);
});
```

您可以像如下這樣顯示 `name` 變數的內容：

```blade
Hello, {{ $name }}.
```

> Blade 的 {{ }} 語句將被 PHP 的 htmlspecialchars 函式自動轉義以防範 XSS 攻擊。

不僅僅可以顯示傳遞給檢視的變數的內容，您亦可輸出任何 `PHP` 函式的結果。事實上，您可以在 `Blade` 模板的回顯語句放置任何 `PHP` 程式碼：

```blade
The current UNIX timestamp is {{ time() }}.
```

#### 顯示非轉義字元

預設情況下，`Blade {{ }}` 語句將被 `PHP` 的 `htmlspecialchars` 函式自動轉義以防範 `XSS` 攻擊。如果您不想您的資料被轉義，那麼您可使用如下的語法：

```blade
Hello, {!! $name !!}.
```

> 在應用中顯示使用者提供的資料時請格外小心，請儘可能的使用轉義和雙引號語法來防範 XSS 攻擊。

#### 渲染 JSON

有時，為了初始化一個 `JavaScript` 變數，您可能會向檢視傳遞一個數組並將其渲染成 `JSON`。例如：

```
<script>
    var app = <?php echo json_encode($array); ?>;
</script>
```

當然，您亦可使用 `@json` `Blade` 指令來代替手動呼叫 `json_encode` 方法。`@json` 指令的引數和 `PHP` 的 `json_encode` 函式一致：

```html
<script>
    var app = @json($array);

    var app = @json($array, JSON_PRETTY_PRINT);
</script>
```

> 使用 @json 指令時，您應該只渲染已經存在的變數為 JSON。Blade 模板是基於正則表示式的，如果嘗試將一個複雜表示式傳遞給 @json 指令可能會導致無法預測的錯誤。

#### HTML 實體編碼

預設情況下，`Blade` 將會對 `HTML` 實體進行雙重編碼。如果您想要禁用此舉，您可以監聽 `BootApplication` 事件，並呼叫 `Blade::withoutDoubleEncoding` 方法：

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

由於許多 `JavaScript` 框架也使用「花括號」來標識將顯示在瀏覽器中的表示式，因此，您可以使用 `@` 符號來表示 `Blade` 渲染引擎應當保持不變。例如：

```blade
<h1>Laravel</h1>

Hello, @{{ name }}.
```

在這個例子中，`@` 符號將被 `Blade` 移除；當然，`Blade` 將不會修改 `{{ name }}` 表示式，取而代之的是 `JavaScript` 模板來對其進行渲染。
`@` 符號也用於轉義 `Blade` 指令：

```
{{-- Blade --}}
@@json()

<!-- HTML 輸出 -->
@json()
```

如果您在模板中顯示很大一部分 `JavaScript` 變數，您可以將 `HTML` 嵌入到 `@verbatim` 指令中，這樣，您就不需要在每一個 `Blade` 回顯語句前新增 `@` 符號：

```blade
@verbatim
    <div class="container">
        Hello, {{ name }}.
    </div>
@endverbatim
```

### 流程控制

除了模板繼承和顯示資料以外，`Blade` 還為常見的 `PHP` 控制結構提供了便捷的快捷方式，例如條件語句和迴圈。這些快捷方式為 `PHP` 控制結構提供了一個非常清晰、簡潔的書寫方式，同時，還與 `PHP` 中的控制結構保持了相似的語法特性。

#### If 語句

您可以使用 `@if`，`@elseif`，`@else` 和 `@endif` 指令構造 `if` 語句。這些指令功能與它們所對應的 `PHP` 語句完全一致：

```blade
@if (count($records) === 1)
    I have one record!
@elseif (count($records) > 1)
    I have multiple records!
@else
    I don't have any records!
@endif
```

為了方便，`Blade` 還提供了一個 `@unless` 指令：

```blade
@unless (is_signed_in())
    You are not signed in.
@endunless
```

除了已經討論過了的條件指令外，`@isset` 和 `@empty` 指令亦可作為它們所對應的 `PHP` 函式的快捷方式：

```blade
@isset($records)
    // $records 已經定義但不為空
@endisset

@empty($records)
    // $records 為空……
@endempty
```

#### 區塊指令

您可以使用 `@hasSection` 指令來判斷區塊是否有內容：

```blade
@hasSection('navigation')
    <div class="pull-right">
        @yield('navigation')
    </div>

    <div class="clearfix"></div>
@endif
```

您可以使用 `@sectionMissing` 指令來判斷區塊是否沒有內容：

```blade
@sectionMissing('navigation')
    <div class="pull-right">
        @include('default-navigation')
    </div>
@endif
```

#### 環境指令

您可以使用 `@production` 指令來判斷應用是否處於生產環境：

```blade
@production
    // 生產環境特定內容……
@endproduction
```

或者，您可以使用 `@env` 指令來判斷應用是否運行於指定的環境：

```blade
@env('staging')
    // 應用運行於「staging」環境……
@endenv

@env(['staging', 'production'])
    // 應用運行於 「staging」環境或生產環境……
@endenv
```

#### Switch 語句

您可使用 `@switch`，`@case`，`@break`，`@default` 和 `@endswitch` 語句來構造 `Switch` 語句：

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

#### 迴圈

除了條件語句，`Blade` 還提供了與 `PHP` 迴圈結構功能相同的指令。同樣，這些語句的功能和它們所對應的 `PHP` 語法一致：

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

> 迴圈時，您可以使用 迴圈變數 去獲取有關迴圈的有價值的資訊，例如，您處於迴圈的第一個迭代亦或是處於最後一個迭代。

在使用迴圈的時候，您可以終止迴圈或跳過當前迭代：

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

您可以在指令的單獨一行中宣告一個條件語句：

```blade
@foreach ($users as $user)
    @continue($user->type == 1)

    <li>{{ $user->name }}</li>

    @break($user->number == 5)
@endforeach
```

#### Loop 變數

迴圈時，迴圈內部可以使用 `$loop` 變數。該變數提供了訪問一些諸如當前的迴圈索引和此次迭代是首次或是末次這樣的資訊的方式：

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

如果您在巢狀迴圈中，您可以使用迴圈的 `$loop` 的變數的 `parent` 屬性訪問父級迴圈：

```blade
@foreach ($users as $user)
    @foreach ($user->posts as $post)
        @if ($loop->parent->first)
            This is first iteration of the parent loop.
        @endif
    @endforeach
@endforeach
```

`$loop` 變數還包含各種各樣有用的屬性：

| 屬性 | 備註 |
|:--:|:--:|
| `$loop->index` | 當前迭代的索引（從 0 開始）。|
| `$loop->iteration` | 當前迴圈的迭代次數（從 1 開始）。|
| `$loop->remaining` | 迴圈剩餘的迭代次數。|
| `$loop->count` | 被迭代的陣列的元素個數。|
| `$loop->first` | 當前迭代是否是迴圈的首次迭代。|
| `$loop->last` | 當前迭代是否是迴圈的末次迭代。|
| `$loop->even` | 當前迴圈的迭代次數是否是偶數。|
| `$loop->odd` | 當前迴圈的迭代次數是否是奇數。|
| `$loop->depth` | 當前迴圈的巢狀深度。|
| `$loop->parent` | 巢狀迴圈中的父級迴圈。|

#### 註釋

`Blade` 也允許您在檢視中定義註釋。但是和 `HTML` 註釋不同， `Blade` 註釋不會被包含在應用返回的 `HTML` 中：

```blade
{{-- This comment will not be present in the rendered HTML --}}
```

#### PHP

在許多情況下，嵌入 `PHP` 程式碼到您的檢視中是很有用的。您可以在模板中使用 `Blade` 的 `@php` 指令執行原生的 `PHP` 程式碼塊：

```blade
@php
    //
@endphp
```

> 儘管 Blade 提供了這個功能，頻繁使用它可能使得您的模板中嵌入過多的邏輯。

#### @once 指令

`@once` 指令允許您定義模板的一部分內容，這部分內容在每一個渲染週期中只會被計算一次。
該指令在使用 `堆疊` 推送一段特定的 `JavaScript` 程式碼到頁面的頭部環境下是很有用的。
例如，如果您想要在迴圈中渲染一個特定的 `元件`，您可能希望僅在元件渲染的首次推送 `JavaScript` 程式碼到頭部：

```blade
@once
    @push('scripts')
        <script>
            // 您自定義的 JavaScript 程式碼
        </script>
    @endpush
@endonce
```

### 元件及插槽

元件和插槽的作用與片段（Section）和佈局（Layout）類似。不過，有些人可能認為元件和插槽使用起來更加方便。Hyperf 支援兩種編寫元件的方法：基於類的元件和匿名元件。

我們可以通過建立一個繼承 `\Hyperf\ViewEngine\Component\Component::class` 類的 class 來定義一個類元件。以下將通過建立一個簡單的 `Alert` 元件來向你說明如何使用元件。

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

#### 手動註冊元件

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

或者在擴充套件包中的 `ConfigProvider` 中

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

註冊元件後，你將可以通過 HTML 標籤別名來使用它：

```html
<x-alert/>
<x-package-alert/>
```

#### 顯示元件

你可以在任一 Blade 模板中使用 Blade 元件標籤來顯示元件。Blade 元件標籤以 `x-` 開頭，後面接上元件的名稱。

```html
<x-alert/>
<x-package-alert/>
```

#### 元件傳參

你可以使用 HTML 屬性將資料傳遞給 Blade 元件。普通的值可以通過簡單的 HTML 屬性傳遞，而 PHP 表示式及變數應當通過以 `:` 為字首的屬性傳遞：

```html
<x-alert type="error" :message="$message"/>
```

!> 注意：你可以在元件類的建構函式中定義元件所需的資料。元件類中的所有公共屬性都將自動傳遞給元件檢視。不必通過元件類的 `render` 方法傳遞。渲染元件時，可以通過變數名稱來獲取元件類公共屬性的內容。

#### 元件方法

除了可獲取元件類的公共屬性外，還可以在元件檢視中執行元件類上的任何公共方法。例如，某元件具有一個 `isSelected` 方法：

```php
    /**
     * 判斷給定選項是否為當前選項
     *
     * @param  string  $option
     * @return bool
     */
    public function isSelected($option)
    {
        return $option === $this->selected;
    }
```

你可以通過呼叫與方法名稱相同的變數來執行該方法：

```html
    <option {{ $isSelected($value) ? 'selected="selected"' : '' }} value="{{ $value }}">
        {{ $label }}
    </option>
```

#### 附加依賴項

如果你的元件需要依賴其他的類，則應當在元件所有資料屬性之前列出它們，它們將會被容器自動注入：
```php
    use App\AlertCreator;
    /**
     * 建立元件例項
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

#### 管理屬性

我們已經瞭解瞭如何將資料屬性傳遞給元件。然而，有時候我們可能需要指定其他的 HTML 屬性（如 `class`），這些屬性不是元件所需要的資料。這種情況下，我們將會想要將這些屬性向下傳遞到元件模板的根元素。例如，我們要渲染一個 `alert` 元件，如下所示：

```html
    <x-alert type="error" :message="$message" class="mt-4"/>
```

所有不屬於元件建構函式的屬性都將自動新增到元件的「屬性包」中。該屬性包將會通過 `$attributes` 變數傳遞給元件檢視。通過輸出此變數，即可在元件中呈現所有屬性：

```html
    <div {{ $attributes }}>
        <!-- 元件內容 -->
    </div>
```

#### 獲取屬性

您可以使用 `get()` 方法獲取特定的屬性值。此方法接受屬性名稱作為第一個引數(第二個引數為預設值)，並將返回其值。

```html
    <div class="{{ $attributes->get("class", "default") }}">
        <!-- 元件內容 -->
    </div>
```

#### 檢測屬性

您可以使用 `has()` 方法獲取特定的屬性值。此方法接受屬性名稱作為引數，並將返回布林值。

```html
    @if($attributes->has("class"))
        <div class="{{ $attributes->get("class") }}">
            <!-- 元件內容 -->
        </div>
    @endif
```

#### 合併屬性

某些時候，你可能需要指定屬性的預設值，或將其他值合併到元件的某些屬性中。為此，你可以使用屬性包的 `merge` 方法：

```html
    <div {{ $attributes->merge(['class' => 'alert alert-'.$type]) }}>
        {{ $message }}
    </div>
```

假設我們如下方所示使用該元件：

```html
    <x-alert type="error" :message="$message" class="mb-4"/>
```

最終呈現的元件 HTML 將如下所示：

```html
    <div class="alert alert-error mb-4">
        <!-- $message 變數的內容 -->
    </div>
```

預設情況下，只會合併 `class` 屬性，其他屬性將會直接進行覆蓋，會出現如下情況：

```blade
// 定義
<div {{ $attributes->merge(['class' => 'alert alert-'.$type, 'other-attr' => 'foo']) }}>{{ $message }}</div>
// 使用
<x-alert type="error" :message="$message" class="mb-4" other-attr="bar"/>
// 呈現
<div class="alert alert-error mb-4" other-attr="bar"><!-- $message 變數的內容 --></div>
```

如上述情況，需要將 `other-attr` 屬性也合併的話，可以使用以下方式，在 `merge()` 方法中新增第二個引數 `true` ：

```blade
// 定義
<div {{ $attributes->merge(['class' => 'alert alert-'.$type, 'other-attr' => 'foo'], true) }}>{{ $message }}</div>
// 使用
<x-alert type="error" :message="$message" class="mb-4" other-attr="bar"/>
// 呈現
<div class="alert alert-error mb-4" other-attr="foo bar"><!-- $message 變數的內容 --></div>
```

#### 插槽

通常，你需要通過 `slots` 向元件傳遞附加內容。 假設我們建立的 `alert` 元件具有以下標記：

```html
    <!-- /storage/view/components/alert.blade.php -->

    <div class="alert alert-danger">
        {{ $slot }}
    </div>
```

我門可以通過向元件注入內容的方式，將內容傳遞到 `slots` ：

```html
    <x-alert>
        <strong>Whoops!</strong> Something went wrong!
    </x-alert>
```

有時候一個元件可能需要在它內部的不同位置放置多個不同的插槽。我們來修改一下 alert 元件，使其允許注入 `title` 。

```html
    <!-- /storage/view/components/alert.blade.php -->

    <span class="alert-title">{{ $title }}</span>

    <div class="alert alert-danger">
        {{ $slot }}
    </div>
```

你可以使用 `x-slot` 標籤來定義一個命名插槽的內容。而不在 `x-slot` 標籤中的其它內容都將傳遞給 `$slot` 變數中的元件：

```html
    <x-alert>
        <x-slot name="title">
            Server Error
        </x-slot>

        <strong>Whoops!</strong> Something went wrong!
    </x-alert>
```

#### 內聯元件

對於小型元件而言，管理元件類和元件檢視模板可能會很麻煩。因此，您可以從 `render` 方法中返回元件的內容：

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

#### 匿名元件

與行內元件相同，匿名元件提供了一個通過單個檔案管理元件的機制。然而，匿名元件使用的是一個沒有關聯類的單一檢視檔案。要定義一個匿名元件，您只需將 Blade 模板置於 `/storage/view/components` 目錄下。
例如，假設您在 `/storage/view/components/alert.blade.php` 中定義了一個元件：

```html
    <x-alert/>
```

如果元件在 `components` 目錄的子目錄中，您可以使用 `.` 字元來指定其路徑。例如，假設元件被定義在 `/storage/view/components/inputs/button.blade.php` 中，您可以像這樣渲染它：

```html
    <x-inputs.button/>
```

#### 匿名元件資料及屬性

由於匿名元件沒有任何關聯類，您可能想要區分哪些資料應該被作為變數傳遞給元件，而哪些屬性應該被存放於 [屬性包](#管理屬性) 中。

您可以在元件的 Blade 模板的頂層使用 `@props` 指令來指定哪些屬性應該作為資料變數。元件中的其他屬性都將通過屬性包的形式提供。如果您想要為某個資料變數指定一個預設值，您可以將屬性名作為陣列鍵，預設值作為陣列值來實現：

```blade
    <!-- /storage/view/components/alert.blade.php -->

    @props(['type' => 'info', 'message'])

    <div {{ $attributes->merge(['class' => 'alert alert-'.$type]) }}>
        {{ $message }}
    </div>
```

#### 動態元件

有時，您可能需要渲染一個元件，但在執行前不知道要渲染哪一個。這種情況下，您可以使用內建的 `dynamic-component` 元件來渲染一個基於值或變數的元件：

```html
    <x-dynamic-component :component="$componentName" class="mt-4" />
```

#### 元件自動載入

預設情況下，`App\View\Component\` 及 `components.` 下的元件會自動註冊。你也可以通過配置檔案修改這個配置：

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

## 檢視空間

通過定義檢視空間，可以方便的在你的擴充套件包中使用檢視檔案，只需要在 `ConfigProvider` 中新增一行配置即可：

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

在安裝擴充套件包之後，可以通過在專案的 `/storage/view/vendor/package-name` 中定義相同路徑的檢視檔案，來覆蓋擴充套件包中的檢視。

## 可選中介軟體

- Hyperf\ViewEngine\Http\Middleware\ShareErrorsFromSession::class

自動將 `session` 中的 `errors` 共享給檢視，依賴 `hyperf/session` 元件

- Hyperf\ViewEngine\Http\Middleware\ValidationExceptionHandle::class

自動捕捉 `validation` 中的異常加入到 `session` 中，依賴 `hyperf/session` 和 `hyperf/validation` 元件

## 其他命令

自動安裝 `view-engine`、`translation` 和 `validation` 元件相關配置

```
php bin/hyperf.php view:publish
```
