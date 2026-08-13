# Internationalization

Hyperf provides very friendly support for internationalization, allowing your project to support multiple languages.

# Installation

```bash
composer require hyperf/translation
```

> This component is an independent component with no framework-related dependencies and can be independently reused in other projects or frameworks.

# Language Files

By default, Hyperf's language files are placed under `storage/languages`. You can also change the folder for language files in `config/autoload/translation.php`. Each language corresponds to a subdirectory, for example, `en` refers to the English language file, and `zh_CN` refers to the Simplified Chinese language file. You can create new language folders and language files in them according to your actual needs. An example is as follows:

```
/storage
    /languages
        /en
            messages.php
        /zh_CN
            messages.php
```

All language files return an array, where the keys of the array are strings:

```php
<?php
// storage/languages/en/messages.php

return [
    'welcome' => 'Welcome to our application',
];
```

## Configure Language Environment

### Configure Default Language Environment

All configurations related to the internationalization component are set in the `config/autoload/translation.php` configuration file, which you can modify according to your actual needs.

```php
<?php
// config/autoload/translation.php

return [
    // Default language
    'locale' => 'zh_CN',
    // Fallback language, used when the language text of the default language is not provided
    'fallback_locale' => 'en',
    // Folder where language files are stored
    'path' => BASE_PATH . '/storage/languages',
];
```

### Configure Temporary Language Environment

```php
<?php

use Hyperf\Di\Annotation\Inject;
use Hyperf\Contract\TranslatorInterface;

class FooController
{
    #[Inject]
    private TranslatorInterface $translator;
    
    public function index()
    {
        // Only valid within the current request or coroutine lifecycle
        $this->translator->setLocale('zh_CN');
    }
}
```

# Translating Strings

## Translate via TranslatorInterface

You can directly inject `Hyperf\Contract\TranslatorInterface` and call the `trans` method of the instance to implement string translation:

```php
<?php

use Hyperf\Di\Annotation\Inject;
use Hyperf\Contract\TranslatorInterface;

class FooController
{
    #[Inject]
    private TranslatorInterface $translator;
    
    public function index()
    {
        return $this->translator->trans('messages.welcome', [], 'zh_CN');
    }
}
```

## Translate via Global Function

You can also use the global functions `__()` or `trans()` to translate strings.
The first argument of the function uses the `key` (referring to the key used as the translated string) or the `file.key` format.

```php
echo __('messages.welcome');
echo trans('messages.welcome');
```

# Defining Placeholders in Translated Strings

You can also define placeholders in language strings, where all placeholders are prefixed with `:`. For example, use the username as a placeholder:

```php
<?php
// storage/languages/en/messages.php

return [
    'welcome' => 'Welcome :name',
];
```

Use the second argument of the function to replace the placeholder:

```php
echo __('messages.welcome', ['name' => 'Hyperf']);
```

If the placeholder is all uppercase, or the first letter is uppercase, the translated string will also be in the corresponding uppercase form:

```php
'welcome' => 'Welcome, :NAME', // Welcome, HYPERF
'goodbye' => 'Goodbye, :Name', // Goodbye, Hyperf
```

# Handling Plurals

Different languages have different plural rules. In Chinese, we might not pay much attention to this, but we need to handle plural forms when translating other languages. We can use the `「pipe」` character to distinguish between the singular and plural forms of a string:

```php
'apples' => 'There is one apple|There are many apples',
```

You can also specify numerical ranges to create more complex plural rules:

```php
'apples' => '{0} There are none|[1,19] There are some|[20,*] There are many',
```

After defining the plural rules using the `「pipe」` character, you can use the global function `trans_choice` to obtain the string text for a given `「quantity」`. In the example below, because the quantity is greater than `1`, the plural form of the translated string is returned:

```php
echo trans_choice('messages.apples', 10);
```

Of course, in addition to the global function `trans_choice()`, you can also use the `transChoice` method of `Hyperf\Contract\TranslatorInterface`.
