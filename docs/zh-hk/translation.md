# 國際化

Hyperf 對國際化的支持是非常友好的，允許讓您的項目支持多種語言。

# 安裝

```bash
composer require hyperf/translation
```

> 該組件為一個獨立組件，無框架相關依賴，可獨立複用於其它項目或框架。

# 語言文件

Hyperf 的語言文件默認都放在 `storage/languages` 下面，您也可以在 `config/autoload/translation.php` 內更改語言文件的文件夾，每種語言對應其中的一個子文件夾，例如 `en` 指英文語言文件，`zh_CN` 指中文簡體的語言文件，你可以按照實際需要創建新的語言文件夾和裏面的語言文件。示例如下：

```
/storage
    /languages
        /en
            messages.php
        /zh_CN
            messages.php
```

所有的語言文件都是返回一個數組，數組的鍵是字符串類型的：

```php
<?php
// storage/languages/en/messages.php

return [
    'welcome' => 'Welcome to our application',
];
```

## 配置語言環境

### 配置默認語言環境

關於國際化組件的相關配置都是在 `config/autoload/translation.php` 配置文件裏設定的，你可以按照實際需要修改它。

```php
<?php
// config/autoload/translation.php

return [
    // 默認語言
    'locale' => 'zh_CN',
    // 回退語言，當默認語言的語言文本沒有提供時，就會使用回退語言的對應語言文本
    'fallback_locale' => 'en',
    // 語言文件存放的文件夾
    'path' => BASE_PATH . '/storage/languages',
];
```

### 配置臨時語言環境

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
        // 只在當前請求或協程生命週期有效
        $this->translator->setLocale('zh_CN');
    }
}
```

# 翻譯字符串

## 通過 TranslatorInterface 翻譯

可直接通過注入 `Hyperf\Contact\TranslatorInterface` 並調用實例的 `trans` 方法實現對字符串的翻譯：

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

## 通過全局函數翻譯

您也可以通過全局函數 `__()` 或 `trans()` 來對字符串進行翻譯。   
函數的第一個參數使用 `鍵`（指使用翻譯字符串作為鍵的鍵） 或者是 `文件. 鍵` 的形式。

```php
echo __('messages.welcome');
echo trans('messages.welcome');
```

# 翻譯字符串中定義佔位符

您也可以在語言字符串中定義佔位符，所有的佔位符使用 `:` 作為前綴。例如，把用户名作為佔位符：

```php
<?php
// storage/languages/en/messages.php

return [
    'welcome' => 'Welcome :name',
];
```

替換佔位符使用函數的第二個參數：

```php
echo __('messages.welcome', ['name' => 'Hyperf']);
```

如果佔位符全部是大寫字母，或者是首字母大寫。那麼翻譯過來的字符串也會是相應的大寫形式：

```php
'welcome' => 'Welcome, :NAME', // Welcome, HYPERF
'goodbye' => 'Goodbye, :Name', // Goodbye, Hyperf
```

# 處理複數

不同語言的複數規則是不同的，在中文中可能不太關注這一點，但在翻譯其它語言時我們需要處理複數形式的用詞。我們可以使用 `「管道」` 字符，可以用來區分字符串的單數和複數形式：

```php
'apples' => 'There is one apple|There are many apples',
```

也可以指定數字範圍，創建更加複雜的複數規則：

```php
'apples' => '{0} There are none|[1,19] There are some|[20,*] There are many',
```

使用 `「管道」` 字符，定義好複數規則後，就可以使用全局函數 `trans_choice` 來獲得給定 `「數量」` 的字符串文本。在下面的例子中，因為數量大於  `1`，所以就會返回翻譯字符串的複數形式：

```php
echo trans_choice('messages.apples', 10);
```

當然除了全局函數 `trans_choice()`，您也可以使用 `Hyperf\Contract\TranslatorInterface` 的 `transChoice` 方法。
