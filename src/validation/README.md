# Hyperf Validation

## About

[hyperf/validation](https://github.com/hyperf/validation) 组件衍生于 `Laravel Validation` 组件的，我们对它进行了一些改造，大部分功能保持了相同。在这里感谢一下 Laravel 开发组，实现了如此强大好用的 Validation 组件。

## Installation

```
composer require hyperf/validation
```

## Config

### Publish config file

```
# 发布国际化配置，已经发布过国际化配置可以省略
php bin/hyperf.php vendor:publish hyperf/translation

php bin/hyperf.php vendor:publish hyperf/validation
```

### Configuration path

```
your/config/path/autoload/translation.php
```

### Configuration

```php
<?php
return [
    'locale' => 'zh_CN',
    'fallback_locale' => 'en',
    'path' => BASE_PATH . '/storage/languages',
];
```

### Exception handler

```php
<?php
return [
    'handler' => [
        'http' => [
            \Hyperf\Validation\ValidationExceptionHandler::class,
        ],
    ],
];
```

### Validation middleware

```php
<?php
return [
    'http' => [
        \Hyperf\Validation\Middleware\ValidationMiddleware::class,
    ],
];
```

## Usage

### Generate form request

Command:
```
php bin/hyperf.php gen:request FooRequest
```

Usage:
```php
class IndexController
{
    public function foo(FooRequest $request)
    {
        $request->input('foo');
    }
    
    public function bar(RequestInterface $request)
    {
        $factory = $this->container->get(\Hyperf\Validation\Contract\ValidatorFactoryInterface::class);

        $factory->extend('foo', function ($attribute, $value, $parameters, $validator) {
            return $value == 'foo';
        });

        $factory->replacer('foo', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':foo', $attribute, $message);
        });

        $validator = $factory->make(
            $request->all(),
            [
                'name' => 'required|foo',
            ],
            [
                'name.foo' => ':foo is not foo',
            ]
        );

        if (!$validator->passes()) {
             $validator->errors();
        }
    }
}
```
