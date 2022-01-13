# View

View rendering is implemented by [hyperf/view](https://github.com/hyperf/view) component. The component supports five different templating engines; `Blade`, `Smarty`, `Twig`, ` Plates` and `ThinkTemplate`.

## Installation

```bash
composer require hyperf/view
```

## Configuration

The configuration file of the view component is located in `config/autoload/view.php`, if the configuration file does not exist, the following command can be executed to generate the configuration file:

```bash
php bin/hyperf.php vendor:publish hyperf/view
```

The following configuration options are available:

| Configuration       | Type     | Default Value                            | Remarks                      |
| :-----------------: | :------: | :--------------------------------------: | :--------------------------: |
| engine              | string   | Hyperf\View\Engine\BladeEngine::class    | View rendering engine        |
| mode                | string   | Mode::TASK                               | View rendering mode          |
| config.view_path    | string   | None                                     | Default address of view file |
| config.cache_path   | string   | None                                     | View file cache address      |

Example configuration file format:

```php
<?php
declare(strict_types=1);

use Hyperf\View\Mode;
use Hyperf\View\Engine\BladeEngine;

return [
    // The rendering engine used
    'engine' => BladeEngine::class,
    // If you don't fill it in, the default is Task mode, it is recommended to use Task mode
    'mode' => Mode::TASK,
    'config' => [
        // If the following folder does not exist, please create it yourself
        'view_path' => BASE_PATH.'/storage/view/',
        'cache_path' => BASE_PATH.'/runtime/view/',
    ],
];
```

### Task Mode

When using the `Task` mode, the [hyperf/task](https://github.com/hyperf/task) component must be installed and the `task_enable_coroutine` must be configured as `false`, otherwise there will be a problem of coroutine data consistency. Please refer to the [task](zh-cn/task.md) component documentation.

In addition, in the `Task` mode the view rendering work is done by a `Task Worker` process while the request processing in the controller is completed by a `Worker` process. This means that it's not possible to access context dependent data objects such as `Request` and `Session` directly from the view. If you need to use context dependent data in your views, make sure you pass the data from the controller via the `render` method.


### Sync mode

If you use the `Sync` mode to render the view, please ensure that the relevant engine is coroutine safe, otherwise there will be data consistency problems. It is recommended to use the more data-safe `Task` mode.

### Configure static resources

If you want `Swoole` to manage static resources, please add the following configuration in the `config/autoload/server.php` configuration.

```
return [
    'settings' => [
        ...
        // static resources
        'document_root' => BASE_PATH.'/public',
        'enable_static_handler' => true,
    ],
];

```

## View rendering engine

The current officially supported rendering engines are `Blade`, `Smarty`, `Twig`, `Plates` and `ThinkTemplate`. The templating engine will not be automatically installed when [hyperf/view](https://github.com/hyperf/view) is installed. You need to install the corresponding templating engine in addition to the view package.

### Install Blade Engine

```bash
composer require hyperf/view-engine
```

For details, please refer to the [view engine documentation](en/view-engine.md).

Or use

> duncan3dc/blade uses Laravel's Support library, so some functions will be incompatible, so it is not recommended for the time being

```bash
composer require duncan3dc/blade
```

### Install Smarty Engine

```bash
composer require smarty/smarty
```

### Install Twig Engine

```bash
composer require twig/twig
```

### Install Plates Engine

```bash
composer require league/plates
```

### Install ThinkTemplate Engine

```bash
composer require sy-records/think-template
```

### Access other templates

Suppose we want to connect a virtual template engine named `TemplateEngine`, then we need to create the corresponding `TemplateEngine` class anywhere and implement the `Hyperf\View\Engine\EngineInterface` interface.

```php
<?php

declare(strict_types=1);

namespace App\Engine;

use Hyperf\View\Engine\EngineInterface;

class TemplateEngine implements EngineInterface
{
    public function render($template, $data, $config): string
    {
        // instantiate an instance of the corresponding template engine
        $engine = new TemplateInstance();
        // and call the corresponding rendering method
        return $engine->render($template, $data);
    }
}

```

Then modify the configuration of the view component:

```php
<?php

use App\Engine\TemplateEngine;

return [
    // Change the engine parameter to your custom template engine class
    'engine' => TemplateEngine::class,
    'mode' => Mode::TASK,
    'config' => [
        'view_path' => BASE_PATH.'/storage/view/',
        'cache_path' => BASE_PATH.'/runtime/view/',
    ],
];
```

## Use

The following takes `BladeEngine` as an example. First, create the view file `index.blade.php` in the corresponding directory.

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

Obtain the `Hyperf\View\Render` instance in the controller, then call the `render` method and pass the view file address `index` and `rendering data`. The file address ignores the suffix of the view file.

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
        return $render->render('index', ['name' =>'Hyperf']);
    }
}

```

Visit the corresponding URL to get the view page as shown below:

```
Hello, Hyperf. You are using blade template now.
```
