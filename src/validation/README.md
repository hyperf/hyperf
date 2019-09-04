# Hyperf Validation


## About

[hyperf/validation](https://github.com/hyperf-cloud/validation) 组件衍生于 `Laravel Validation` 组件的，我们对它进行了一些改造，大部分功能保持了相同。在这里感谢一下 Laravel 开发组，实现了如此强大好用的 Validation 组件。

## Install

```
composer require hyperf/validation

```

## Config


### publish config
```
php bin/hyperf.php  vendor:publish hyperf/validation

```

### config path

```
your/config/path/autoload/translation.php

```

### config content

```
<?php

declare(strict_types=1);

/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

return [
    'locale'          => 'en',   
    'fallback_locale' => '',
    'lang'            => BASE_PATH . '/resources/lang', 
];

```

### exception handler

```
<?php

declare(strict_types=1);

/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

return [
    'handler' => [
        'http' => [
            \Hyperf\Validation\ValidationExceptionHandler::class,
        ],
    ],
];

```

### validation middleware

```
<?php

declare(strict_types=1);

/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

return [
    'http' => [
        \Hyperf\Validation\Middleware\ValidationMiddleware::class,
    ],
];

```


## Usage


### gen request

```
php bin/hyperf.php gen:request FooRequest
```


```
class IndexController extends Controller
{
   

    public function foo(FooRequest $request)
    {
        // todo
    }
}


```