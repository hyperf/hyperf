# 國際化

Hyperf 對國際化的支援是非常友好的，允許讓您的專案支援多種語言。

# 安裝

```bash
composer require hyperf/translation
```

> 該元件為一個獨立元件，無框架相關依賴，可獨立複用於其它專案或框架。

# 語言檔案

Hyperf 的語言檔案預設都放在 `storage/languages` 下面，您也可以在 `config/autoload/translation.php` 內更改語言檔案的資料夾，每種語言對應其中的一個子資料夾，例如 `en` 指英文語言檔案，`zh_CN` 指中文簡體的語言檔案，你可以按照實際需要建立新的語言資料夾和裡面的語言檔案。示例如下：

```
/storage
    /languages
        /en
            messages.php
        /zh_CN
            messages.php
```

所有的語言檔案都是返回一個數組，陣列的鍵是字串型別的：

```php
<?php
// storage/languages/en/messages.php

return [
    'welcome' => 'Welcome to our application',
];
```

## 配置語言環境

### 配置預設語言環境

關於國際化元件的相關配置都是在 `config/autoload/translation.php` 配置檔案裡設定的，你可以按照實際需要修改它。

```php
<?php
// config/autoload/translation.php

return [
    // 預設語言
    'locale' => 'zh_CN',
    // 回退語言，當預設語言的語言文字沒有提供時，就會使用回退語言的對應語言文字
    'fallback_locale' => 'en',
    // 語言檔案存放的資料夾
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

# 翻譯字串

## 透過 TranslatorInterface 翻譯

可直接透過注入 `Hyperf\Contact\TranslatorInterface` 並呼叫例項的 `trans` 方法實現對字串的翻譯：

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

## 透過全域性函式翻譯

您也可以透過全域性函式 `__()` 或 `trans()` 來對字串進行翻譯。   
函式的第一個引數使用 `鍵`（指使用翻譯字串作為鍵的鍵） 或者是 `檔案. 鍵` 的形式。

```php
echo __('messages.welcome');
echo trans('messages.welcome');
```

# 翻譯字串中定義佔位符

您也可以在語言字串中定義佔位符，所有的佔位符使用 `:` 作為字首。例如，把使用者名稱作為佔位符：

```php
<?php
// storage/languages/en/messages.php

return [
    'welcome' => 'Welcome :name',
];
```

替換佔位符使用函式的第二個引數：

```php
echo __('messages.welcome', ['name' => 'Hyperf']);
```

如果佔位符全部是大寫字母，或者是首字母大寫。那麼翻譯過來的字串也會是相應的大寫形式：

```php
'welcome' => 'Welcome, :NAME', // Welcome, HYPERF
'goodbye' => 'Goodbye, :Name', // Goodbye, Hyperf
```

# 處理複數

不同語言的複數規則是不同的，在中文中可能不太關注這一點，但在翻譯其它語言時我們需要處理複數形式的用詞。我們可以使用 `「管道」` 字元，可以用來區分字串的單數和複數形式：

```php
'apples' => 'There is one apple|There are many apples',
```

也可以指定數字範圍，建立更加複雜的複數規則：

```php
'apples' => '{0} There are none|[1,19] There are some|[20,*] There are many',
```

使用 `「管道」` 字元，定義好複數規則後，就可以使用全域性函式 `trans_choice` 來獲得給定 `「數量」` 的字串文字。在下面的例子中，因為數量大於  `1`，所以就會返回翻譯字串的複數形式：

```php
echo trans_choice('messages.apples', 10);
```

當然除了全域性函式 `trans_choice()`，您也可以使用 `Hyperf\Contract\TranslatorInterface` 的 `transChoice` 方法。
