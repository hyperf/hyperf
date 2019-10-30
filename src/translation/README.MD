# Hyperf Translation

[hyperf/translation](https://github.com/hyperf/translation) 组件衍生于 `Laravel Translation` 组件的，我们对它进行了一些改造，大部分功能保持了相同。在这里感谢一下 Laravel 开发组，实现了如此强大好用的 Translation 组件。

## Installation

```bash
composer require hyperf/translation
```

## Configuration

### Publish config

```bash
php bin/hyperf.php vendor:publish hyperf/translation
```

Config files:
```
+ ./config/autoload/translation.php
```

### Configuration

```php
<?php
declare(strict_types=1);

return [
    'locale' => 'en',   
    'fallback_locale' => '',
    'path' => BASE_PATH . '/storage/languages', 
];
```

## Usage

```php
$container = ApplicationContext::getContainer();
$translator = $container->get(\Hyperf\Contract\TranslatorInterface::class);
$translator->trans('validation.accepted');
```