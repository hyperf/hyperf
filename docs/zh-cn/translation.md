# 国际化

Hyperf 对国际化的支持是非常友好的，允许让您的项目支持多种语言。

# 安装

```bash
composer require hyperf/translation
```

> 该组件为一个独立组件，无框架相关依赖，可独立复用于其它项目或框架。

# 语言文件

Hyperf 的语言文件默认都放在 `storage/languages` 下面，您也可以在 `config/autoload/translation.php` 内更改语言文件的文件夹，每种语言对应其中的一个子文件夹，例如 `en` 指英文语言文件，`zh_CN` 指中文简体的语言文件，你可以按照实际需要创建新的语言文件夹和里面的语言文件。示例如下：

```
/storage
    /languages
        /en
            messages.php
        /zh_CN
            messages.php
```

所有的语言文件都是返回一个数组，数组的键是字符串类型的：

```php
<?php
// storage/languages/en/messages.php

return [
    'welcome' => 'Welcome to our application',
];
```

## 配置语言环境

### 配置默认语言环境

关于国际化组件的相关配置都是在 `config/autoload/translation.php` 配置文件里设定的，你可以按照实际需要修改它。

```php
<?php
// config/autoload/translation.php

return [
    // 默认语言
    'locale' => 'zh_CN',
    // 回退语言，当默认语言的语言文本没有提供时，就会使用回退语言的对应语言文本
    'fallback_locale' => 'en',
    // 语言文件存放的文件夹
    'path' => BASE_PATH . '/storage/languages',
];
```

### 配置临时语言环境

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
        // 只在当前请求或协程生命周期有效
        $this->translator->setLocale('zh_CN');
    }
}
```

# 翻译字符串

## 通过 TranslatorInterface 翻译

可直接通过注入 `Hyperf\Contact\TranslatorInterface` 并调用实例的 `trans` 方法实现对字符串的翻译：

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

## 通过全局函数翻译

您也可以通过全局函数 `__()` 或 `trans()` 来对字符串进行翻译。   
函数的第一个参数使用 `键`（指使用翻译字符串作为键的键） 或者是 `文件. 键` 的形式。

```php
echo __('messages.welcome');
echo trans('messages.welcome');
```

# 翻译字符串中定义占位符

您也可以在语言字符串中定义占位符，所有的占位符使用 `:` 作为前缀。例如，把用户名作为占位符：

```php
<?php
// storage/languages/en/messages.php

return [
    'welcome' => 'Welcome :name',
];
```

替换占位符使用函数的第二个参数：

```php
echo __('messages.welcome', ['name' => 'Hyperf']);
```

如果占位符全部是大写字母，或者是首字母大写。那么翻译过来的字符串也会是相应的大写形式：

```php
'welcome' => 'Welcome, :NAME', // Welcome, HYPERF
'goodbye' => 'Goodbye, :Name', // Goodbye, Hyperf
```

# 处理复数

不同语言的复数规则是不同的，在中文中可能不太关注这一点，但在翻译其它语言时我们需要处理复数形式的用词。我们可以使用 `「管道」` 字符，可以用来区分字符串的单数和复数形式：

```php
'apples' => 'There is one apple|There are many apples',
```

也可以指定数字范围，创建更加复杂的复数规则：

```php
'apples' => '{0} There are none|[1,19] There are some|[20,*] There are many',
```

使用 `「管道」` 字符，定义好复数规则后，就可以使用全局函数 `trans_choice` 来获得给定 `「数量」` 的字符串文本。在下面的例子中，因为数量大于  `1`，所以就会返回翻译字符串的复数形式：

```php
echo trans_choice('messages.apples', 10);
```

当然除了全局函数 `trans_choice()`，您也可以使用 `Hyperf\Contract\TranslatorInterface` 的 `transChoice` 方法。
