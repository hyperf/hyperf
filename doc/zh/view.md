# 视图

## 安装

```
composer require hyperf/view
```

## 配置

|       配置        |  类型  |                 默认值                 |       备注       |
|:-----------------:|:------:|:--------------------------------------:|:----------------:|
|      engine       | string | Hyperf\View\Engine\BladeEngine::class |   视图渲染引擎   |
|       mode        | string |               Mode::TASK               |   视图渲染模式   |
| config.view_path  | string |                   无                   | 视图文件默认地址 |
| config.cache_path | string |                   无                   | 视图文件缓存地址 |

```php
<?php

declare(strict_types=1);

use Hyperf\View\Mode;
use Hyperf\View\Engine\BladeEngine;

return [
    // 使用的渲染引擎
    'engine' => BladeEngine::class,
    // 不填写则默认为 Task 模式
    'mode' => Mode::TASK,
    'config' => [
        // 若不存在请自行创建
        'view_path' => BASE_PATH . '/storage/view/',
        'cache_path' => BASE_PATH . '/runtime/view/',
    ],
];
```

> 使用 `Task` 模式时，需引入 [hyperf/task](https://github.com/hyperf-cloud/task) 组件且必须配置 `task_enable_coroutine` 为 `false`，否则会出现协程数据混淆的问题，更多请查阅 [Task](zh/task.md) 组件文档。

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

控制器中获取 `Hyperf\View\Render` 示例，然后返回渲染数据即可。

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\View\RenderInterface;

/**
 * @AutoController
 */
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