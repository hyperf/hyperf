# I18n

Hyperf's internationalization support is very friendly, allowing your project to support multiple languages.

# Install

```bash
composer require hyperf/translation
```

> This component is an independent component with no framework-related dependencies, and can be independently reused for other projects or frameworks.

# Language file

The language files of Hyperf are placed under `storage/languages` by default, you can also change the folder of language files in `config/autoload/translation.php`, each language corresponds to a subfolder, such as `en ` refers to the English language file, `zh_CN` refers to the simplified Chinese language file, you can create a new language folder and the language file in it according to your actual needs. An example is as follows:

```
/storage
    /languages
        /en
            messages.php
        /zh_CN
            messages.php
```

All language files return an array whose keys are strings:

```php
<?php
// storage/languages/en/messages.php

return [
    'welcome' => 'Welcome to our application',
];
```

## Configure locale

### Configure default locale

The relevant configuration of the internationalization component is set in the `config/autoload/translation.php` configuration file, you can modify it according to your actual needs.

```php
<?php
// config/autoload/translation.php

return [
    // default language
    'locale' => 'zh_CN',
    // Fallback language, when the language text of the default language is not provided, the corresponding language text of the fallback language will be used
    'fallback_locale' => 'en',
    // Folder where language files are stored
    'path' => BASE_PATH . '/storage/languages',
];
```

### Configure a temporary locale

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
        // Only valid for the current request or coroutine lifetime
        $this->translator->setLocale('zh_CN');
    }
}
```

# Translate string

## Translate via TranslatorInterface

String translation can be done directly by injecting `Hyperf\Contact\TranslatorInterface` and calling the instance's `trans` method:

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

## Translate via global function

You can also translate strings through the global functions `__()` or `trans()`.
The first parameter of the function takes the form of `key` (referring to the key using the translation string as the key) or `file.key`.

```php
echo __('messages.welcome');
echo trans('messages.welcome');
```

# Define placeholders in translation strings

You can also define placeholders in language strings, all placeholders are prefixed with `:`. For example, using the username as a placeholder:

```php
<?php
// storage/languages/en/messages.php

return [
    'welcome' => 'Welcome :name',
];
```

Replace the placeholder using the second parameter of the function:

```php
echo __('messages.welcome', ['name' => 'Hyperf']);
```

If the placeholder is all capital letters, or the first letter is capitalized. Then the translated string will also be in the corresponding uppercase form:

```php
'welcome' => 'Welcome, :NAME', // Welcome, HYPERF
'goodbye' => 'Goodbye, :Name', // Goodbye, HYPERF
```

# Handle complex numbers

Plural rules are different in different languages, which may not be of great concern in Chinese, but when translating other languages, we need to deal with plural forms of words. We can use the `"pipe"` character, which can be used to distinguish singular and plural forms of strings:

```php
'apples' => 'There is one apple|There are many apples',
```

You can also specify a range of numbers to create more complex plural rules:

```php
'apples' => '{0} There are none|[1,19] There are some|[20,*] There are many',
```

Using the `"pipe"` character, once the plural rules have been defined, the global function `trans_choice` can be used to obtain a string literal for a given `"amount"`. In the following example, since the number is greater than `1`, the plural form of the translation string is returned:

```php
echo trans_choice('messages.apples', 10);
```

Of course, in addition to the global function `trans_choice()`, you can also use the `transChoice` method of `Hyperf\Contract\TranslatorInterface`.
