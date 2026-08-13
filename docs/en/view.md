# View

The view component is implemented and provided by [hyperf/view](https://github.com/hyperf/view) to meet your needs for view rendering. By default, the component supports five template engines: `Blade`, `Smarty`, `Twig`, `Plates`, and `ThinkTemplate`.

## Installation

```bash
composer require hyperf/view
```

## Configuration

The configuration file for the View component is located at `config/autoload/view.php`. If the configuration file does not exist, you can execute the following command to generate it:

```bash
php bin/hyperf.php vendor:publish hyperf/view
```

The following is an explanation of the relevant configurations:

| Configuration | Type | Default Value | Note |
|:---:|:---:|:---:|:---:|
| engine | string | Hyperf\View\Engine\BladeEngine::class | View rendering engine |
| mode | string | Mode::TASK | View rendering mode |
| config.view_path | string | None | Default directory for view files |
| config.cache_path | string | None | Default cache directory for view files |

Configuration file format example:

```php
<?php
declare(strict_types=1);

use Hyperf\View\Mode;
use Hyperf\View\Engine\BladeEngine;

return [
    // Rendering engine used
    'engine' => BladeEngine::class,
    // If not filled, Task mode is used by default. Task mode is recommended
    'mode' => Mode::TASK,
    'config' => [
        // Please create the following folders if they do not exist
        'view_path' => BASE_PATH . '/storage/view/',
        'cache_path' => BASE_PATH . '/runtime/view/',
    ],
];
```

### Task Mode

When using `Task` mode, you need to introduce the [hyperf/task](https://github.com/hyperf/task) component and must configure `task_enable_coroutine` to `false`, otherwise, coroutine data confusion issues will occur. For more information, please refer to the [Task](task.md) component documentation.

Additionally, in `Task` mode, view rendering work is completed in the `Task Worker` process, while request processing (i.e., Controller) is completed in the `Worker` process. The work of the two parts is completed by different processes. Therefore, objects or data managed through the context in the `Worker` process, such as `Request` and `Session`, cannot be directly used on the view page. In this case, you need to process data or judgment results in the Controller first, and then pass the data to the view for rendering when calling `render`.

### Sync Mode

If you use `Sync` mode to render views, please ensure that the related engine is coroutine-safe, otherwise, data confusion issues will occur. It is recommended to use the more data-safe `Task` mode.

### Configure Static Resources

If you want `Swoole` to manage static resources, please add the following configuration to your `config/autoload/server.php` configuration.

```
return [
    'settings' => [
        ...
        // Static resources
        'document_root' => BASE_PATH . '/public',
        'enable_static_handler' => true,
    ],
];
```

## View Rendering Engine

The official currently supports five templates: `Blade`, `Smarty`, `Twig`, `Plates`, and `ThinkTemplate`. By default, installing [hyperf/view](https://github.com/hyperf/view) will not automatically install any template engine. You need to install the corresponding template engine yourself according to your own needs. You must install at least one template engine before using it.

### Install Blade Engine

```bash
composer require hyperf/view-engine
```

For detailed methods, see the document [View Engine](view-engine.md)

Or use:

> duncan3dc/blade is not recommended for now because it uses Laravel's Support library, which causes some functions to be incompatible.

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

### Integrate Other Templates

Suppose we want to integrate a virtual template engine named `TemplateEngine`. We need to create the corresponding `TemplateEngine` class anywhere and implement the `Hyperf\View\Engine\EngineInterface` interface.

```php
<?php

declare(strict_types=1);

namespace App\Engine;

use Hyperf\View\Engine\EngineInterface;

class TemplateEngine implements EngineInterface
{
    public function render($template, $data, $config): string
    {
        // Instantiate the instance of the corresponding template engine
        $engine = new TemplateInstance();
        // And call the corresponding rendering method
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
        'view_path' => BASE_PATH . '/storage/view/',
        'cache_path' => BASE_PATH . '/runtime/view/',
    ],
];
```

## Usage

Taking `BladeEngine` as an example, first create a view file `index.blade.php` in the corresponding directory.

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

In the Controller, get the `Hyperf\View\Render` instance, and then call the `render` method and pass the view file address `index` and `rendering data`. The file address ignores the suffix of the view file.

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

Access the corresponding URL to get the view page as shown below:

```
Hello, Hyperf. You are using blade template now.
```
