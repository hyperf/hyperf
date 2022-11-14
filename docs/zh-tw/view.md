# 檢視

檢視元件由 [hyperf/view](https://github.com/hyperf/view) 實現並提供使用，滿足您對檢視渲染的需求，元件預設支援 `Blade` 、 `Smarty` 、 `Twig` 、 `Plates` 和 `ThinkTemplate` 五種模板引擎。

## 安裝

```bash
composer require hyperf/view
```

## 配置

View 元件的配置檔案位於 `config/autoload/view.php`，若配置檔案不存在可執行如下命令生成配置檔案

```bash
php bin/hyperf.php vendor:publish hyperf/view
```

以下為相關配置的說明：

|       配置        |  型別  |                預設值                 |       備註       |
|:-----------------:|:------:|:-------------------------------------:|:----------------:|
|      engine       | string | Hyperf\View\Engine\BladeEngine::class |   檢視渲染引擎   |
|       mode        | string |              Mode::TASK               |   檢視渲染模式   |
| config.view_path  | string |                  無                   | 檢視檔案預設地址 |
| config.cache_path | string |                  無                   | 檢視檔案快取地址 |

配置檔案格式示例：

```php
<?php
declare(strict_types=1);

use Hyperf\View\Mode;
use Hyperf\View\Engine\BladeEngine;

return [
    // 使用的渲染引擎
    'engine' => BladeEngine::class,
    // 不填寫則預設為 Task 模式，推薦使用 Task 模式
    'mode' => Mode::TASK,
    'config' => [
        // 若下列資料夾不存在請自行建立
        'view_path' => BASE_PATH . '/storage/view/',
        'cache_path' => BASE_PATH . '/runtime/view/',
    ],
];
```

### Task 模式

使用 `Task` 模式時，需引入 [hyperf/task](https://github.com/hyperf/task) 元件且必須配置 `task_enable_coroutine` 為 `false`，否則會出現協程資料混淆的問題，更多請查閱 [Task](zh-tw/task.md) 元件文件。

另外，在 `Task` 模式下，檢視渲染工作是在 `Task Worker` 程序中完成的，而請求處理即 Controller 是在 `Worker` 程序完成的，兩部分的工作由不同的程序完成，所以像 `Request`，`Session` 等在 `Worker` 程序通過上下文管理的物件或資料在檢視頁面上是無法直接使用的，這時候就需要您在 Controller 先處理好資料或判斷結果，然後再呼叫 `render` 時傳遞資料到檢視進行資料的渲染。

### Sync 模式

若使用 `Sync` 模式渲染檢視時，請確保相關引擎是協程安全的，否則會出現資料混淆的問題，建議使用更加資料安全的 `Task` 模式。

### 配置靜態資源

如果您希望 `Swoole` 來管理靜態資源，請在 `config/autoload/server.php` 配置中增加以下配置。

```
return [
    'settings' => [
        ...
        // 靜態資源
        'document_root' => BASE_PATH . '/public',
        'enable_static_handler' => true,
    ],
];

```

## 檢視渲染引擎

官方目前支援 `Blade` 、 `Smarty` 、 `Twig` 、 `Plates` 和 `ThinkTemplate` 五種模板，預設安裝 [hyperf/view](https://github.com/hyperf/view) 時不會自動安裝任何模板引擎，需要您根據自身需求，自行安裝對應的模板引擎，使用前必須安裝任一模板引擎。

### 安裝 Blade 引擎

```bash
composer require hyperf/view-engine
```

詳細方式見文件 [檢視引擎](zh-tw/view-engine.md)

或者使用

> duncan3dc/blade 因為使用了 Laravel 的 Support 庫，所以會導致某些函式不相容，暫時不推薦使用

```bash
composer require duncan3dc/blade
```

### 安裝 Smarty 引擎

```bash
composer require smarty/smarty
```

### 安裝 Twig 引擎

```bash
composer require twig/twig
```

### 安裝 Plates 引擎

```bash
composer require league/plates
```

### 安裝 ThinkTemplate 引擎

```bash
composer require sy-records/think-template
```

### 接入其他模板

假設我們想要接入一個虛擬的模板引擎名為 `TemplateEngine`，那麼我們需要在任意地方建立對應的 `TemplateEngine` 類，並實現 `Hyperf\View\Engine\EngineInterface` 介面。

```php
<?php

declare(strict_types=1);

namespace App\Engine;

use Hyperf\View\Engine\EngineInterface;

class TemplateEngine implements EngineInterface
{
    public function render($template, $data, $config): string
    {
        // 例項化對應的模板引擎的例項
        $engine = new TemplateInstance();
        // 並呼叫對應的渲染方法
        return $engine->render($template, $data);
    }
}

```

然後修改檢視元件的配置：

```php
<?php

use App\Engine\TemplateEngine;

return [
    // 將 engine 引數改為您的自定義模板引擎類
    'engine' => TemplateEngine::class,
    'mode' => Mode::TASK,
    'config' => [
        'view_path' => BASE_PATH . '/storage/view/',
        'cache_path' => BASE_PATH . '/runtime/view/',
    ],
];
```

## 使用

以下以 `BladeEngine` 為例，首先在對應的目錄裡建立檢視檔案 `index.blade.php`。

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hyperf</title>
</head>
<body>
Hello, {{ $name }}. You are using blade template now.
</body>
</html>
```

控制器中獲取 `Hyperf\View\Render` 例項，然後呼叫 `render` 方法並傳遞檢視檔案地址 `index` 和 `渲染資料` 即可，檔案地址忽略檢視檔案的字尾名。

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\View\RenderInterface;

#[AutoController]
class ViewController
{
    public function index(RenderInterface $render)
    {
        return $render->render('index', ['name' => 'Hyperf']);
    }
}

```

訪問對應的 URL，即可獲得如下所示的檢視頁面：

```
Hello, Hyperf. You are using blade template now.
```
