# 视图

视图组件由 [hyperf/view](https://github.com/hyperf/view) 实现并提供使用，满足您对视图渲染的需求，组件默认支持 `Blade` 、 `Smarty` 、 `Twig` 、 `Plates` 和 `ThinkTemplate` 五种模板引擎。

## 安装

```bash
composer require hyperf/view
```

## 配置

View 组件的配置文件位于 `config/autoload/view.php`，若配置文件不存在可执行如下命令生成配置文件

```bash
php bin/hyperf.php vendor:publish hyperf/view
```

以下为相关配置的说明：

|       配置        |  类型  |                默认值                 |       备注       |
|:-----------------:|:------:|:-------------------------------------:|:----------------:|
|      engine       | string | Hyperf\View\Engine\BladeEngine::class |   视图渲染引擎   |
|       mode        | string |              Mode::TASK               |   视图渲染模式   |
| config.view_path  | string |                  无                   | 视图文件默认地址 |
| config.cache_path | string |                  无                   | 视图文件缓存地址 |

配置文件格式示例：

```php
<?php
declare(strict_types=1);

use Hyperf\View\Mode;
use Hyperf\View\Engine\BladeEngine;

return [
    // 使用的渲染引擎
    'engine' => BladeEngine::class,
    // 不填写则默认为 Task 模式，推荐使用 Task 模式
    'mode' => Mode::TASK,
    'config' => [
        // 若下列文件夹不存在请自行创建
        'view_path' => BASE_PATH . '/storage/view/',
        'cache_path' => BASE_PATH . '/runtime/view/',
    ],
];
```

### Task 模式

使用 `Task` 模式时，需引入 [hyperf/task](https://github.com/hyperf/task) 组件且必须配置 `task_enable_coroutine` 为 `false`，否则会出现协程数据混淆的问题，更多请查阅 [Task](zh-cn/task.md) 组件文档。

另外，在 `Task` 模式下，视图渲染工作是在 `Task Worker` 进程中完成的，而请求处理即 Controller 是在 `Worker` 进程完成的，两部分的工作由不同的进程完成，所以像 `Request`，`Session` 等在 `Worker` 进程通过上下文管理的对象或数据在视图页面上是无法直接使用的，这时候就需要您在 Controller 先处理好数据或判断结果，然后再调用 `render` 时传递数据到视图进行数据的渲染。

### Sync 模式

若使用 `Sync` 模式渲染视图时，请确保相关引擎是协程安全的，否则会出现数据混淆的问题，建议使用更加数据安全的 `Task` 模式。

### 配置静态资源

如果您希望 `Swoole` 来管理静态资源，请在 `config/autoload/server.php` 配置中增加以下配置。

```
return [
    'settings' => [
        ...
        // 静态资源
        'document_root' => BASE_PATH . '/public',
        'enable_static_handler' => true,
    ],
];

```

## 视图渲染引擎

官方目前支持 `Blade` 、 `Smarty` 、 `Twig` 、 `Plates` 和 `ThinkTemplate` 五种模板，默认安装 [hyperf/view](https://github.com/hyperf/view) 时不会自动安装任何模板引擎，需要您根据自身需求，自行安装对应的模板引擎，使用前必须安装任一模板引擎。

### 安装 Blade 引擎

```bash
composer require hyperf/view-engine
```

详细方式见文档 [视图引擎](zh-cn/view-engine.md)

或者使用

> duncan3dc/blade 因为使用了 Laravel 的 Support 库，所以会导致某些函数不兼容，暂时不推荐使用

```bash
composer require duncan3dc/blade
```

### 安装 Smarty 引擎

```bash
composer require smarty/smarty
```

### 安装 Twig 引擎

```bash
composer require twig/twig
```

### 安装 Plates 引擎

```bash
composer require league/plates
```

### 安装 ThinkTemplate 引擎

```bash
composer require sy-records/think-template
```

### 接入其他模板

假设我们想要接入一个虚拟的模板引擎名为 `TemplateEngine`，那么我们需要在任意地方创建对应的 `TemplateEngine` 类，并实现 `Hyperf\View\Engine\EngineInterface` 接口。

```php
<?php

declare(strict_types=1);

namespace App\Engine;

use Hyperf\View\Engine\EngineInterface;

class TemplateEngine implements EngineInterface
{
    public function render($template, $data, $config): string
    {
        // 实例化对应的模板引擎的实例
        $engine = new TemplateInstance();
        // 并调用对应的渲染方法
        return $engine->render($template, $data);
    }
}

```

然后修改视图组件的配置：

```php
<?php

use App\Engine\TemplateEngine;

return [
    // 将 engine 参数改为您的自定义模板引擎类
    'engine' => TemplateEngine::class,
    'mode' => Mode::TASK,
    'config' => [
        'view_path' => BASE_PATH . '/storage/view/',
        'cache_path' => BASE_PATH . '/runtime/view/',
    ],
];
```

## 使用

以下以 `BladeEngine` 为例，首先在对应的目录里创建视图文件 `index.blade.php`。

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

控制器中获取 `Hyperf\View\Render` 实例，然后调用 `render` 方法并传递视图文件地址 `index` 和 `渲染数据` 即可，文件地址忽略视图文件的后缀名。

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

访问对应的 URL，即可获得如下所示的视图页面：

```
Hello, Hyperf. You are using blade template now.
```
