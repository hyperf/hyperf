# ConfigProvider 機制

ConfigProvider 機制對於 Hyperf 元件化來說是個非常重要的機制，`元件間的解耦` 和 `元件的獨立性` 以及 `元件的可重用性` 都是基於這個機制才得以實現。   

# 什麼是 ConfigProvider 機制 ？

簡單來說，就是每個元件都會提供一個 `ConfigProvider`，通常是在元件的根目錄提供一個 `ConfigProvider` 的類，`ConfigProvider` 會提供對應元件的所有配置資訊，這些資訊都會被 Hyperf 框架在啟動時載入，最終`ConfigProvider` 內的配置資訊會被合併到 `Hyperf\Contract\ConfigInterface` 對應的實現類去，從而實現各個元件在 Hyperf 框架下使用時要進行的配置初始化。   

`ConfigProvider` 本身不具備任何依賴，不繼承任何的抽象類和不要求實現任何的介面，只需提供一個 `__invoke` 方法並返回一個對應配置結構的陣列即可。

# 如何定義一個 ConfigProvider ？

通常來說，`ConfigProvider` 會定義在元件的根目錄下，一個 `ConfigProvider` 類通常如下：

```php
<?php

namespace Hyperf\Foo;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            // 合併到  config/autoload/dependencies.php 檔案
            'dependencies' => [],
            // 合併到  config/autoload/annotations.php 檔案
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            // 預設 Command 的定義，合併到 Hyperf\Contract\ConfigInterface 內，換個方式理解也就是與 config/autoload/commands.php 對應
            'commands' => [],
            // 與 commands 類似
            'listeners' => [],
            // 元件預設配置檔案，即執行命令後會把 source 的對應的檔案複製為 destination 對應的的檔案
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'description of this config file.', // 描述
                    // 建議預設配置放在 publish 資料夾中，檔案命名和元件名稱相同
                    'source' => __DIR__ . '/../publish/file.php',  // 對應的配置檔案路徑
                    'destination' => BASE_PATH . '/config/autoload/file.php', // 複製為這個路徑下的該檔案
                ],
            ],
            // 亦可繼續定義其它配置，最終都會合併到與 ConfigInterface 對應的配置儲存器中
        ];
    }
}
```

## 預設配置檔案說明

在 `ConfigProvider` 中定義好 `publish` 後，可以使用如下命令快速生成配置檔案

```bash
php bin/hyperf.php vendor:publish 包名稱
```

如包名稱為 `hyperf/amqp`，可執行命令來生成 `amqp` 預設的配置檔案
```bash
php bin/hyperf.php vendor:publish hyperf/amqp
```

只建立一個類並不會被 Hyperf 自動的載入，您仍需在元件的 `composer.json` 新增一些定義，告訴 Hyperf 這是一個 ConfigProvider 類需要被載入，您需要在元件內的 `composer.json` 檔案內增加 `extra.hyperf.config` 配置，並指定對應的 `ConfigProvider` 類的名稱空間，如下所示：

```json
{
    "name": "hyperf/foo",
    "require": {
        "php": ">=7.3"
    },
    "autoload": {
        "psr-4": {
            "Hyperf\\Foo\\": "src/"
        }
    },
    "extra": {
        "hyperf": {
            "config": "Hyperf\\Foo\\ConfigProvider"
        }
    }
}
```

定義了之後需執行 `composer install` 或 `composer update` 或 `composer dump-autoload` 等會讓 Composer 重新生成 `composer.lock` 檔案的命令，才能被正常讀取。   

# ConfigProvider 機制的執行流程

關於 `ConfigProvider` 的配置並非一定就是這樣去劃分，這是一些約定成俗的格式，實際上最終如何來解析這些配置的決定權也在於使用者，使用者可透過修改 Skeleton 專案的 `config/container.php` 檔案內的程式碼來調整相關的載入，也就意味著，`config/container.php` 檔案決定了 `ConfigProvider` 的掃描和載入。

# 元件設計規範

由於 `composer.json` 內的 `extra` 屬性在資料不被利用時無其它作用和影響，故這些元件內的定義在其它框架使用時，不會造成任何的干擾和影響，故`ConfigProvider` 是一種僅作用於 Hyperf 框架的機制，對其它沒有利用此機制的框架不會造成任何的影響，這也就為元件的複用打下了基礎，但這也要求在進行元件設計時，必須遵循以下規範：

- 所有類的設計都必須允許透過標準 `OOP` 的使用方式來使用，所有 Hyperf 專有的功能必須作為增強功能並以單獨的類來提供，也就意味著在非 Hyperf 框架下仍能透過標準的手段來實現元件的使用；
- 元件的依賴設計如果可滿足 [PSR 標準](https://www.php-fig.org/psr) 則優先滿足且依賴對應的介面而不是實現類；如 [PSR 標準](https://www.php-fig.org/psr) 沒有包含的功能，則可滿足由 Hyperf 定義的契約庫 [hyperf/contract](https://github.com/hyperf/contract) 內的介面時優先滿足且依賴對應的介面而不是實現類；
- 對於實現 Hyperf 專有功能所增加的增強功能類，通常來說也會對 Hyperf 的一些元件有依賴，那麼這些元件的依賴不應該寫在 `composer.json` 的 `require` 項，而是寫在 `suggest` 項作為建議項存在；
- 元件設計時不應該透過註解進行任何的依賴注入，注入方式應只使用 `建構函式注入` 的方式，這樣同時也能滿足在 `OOP` 下的使用；
- 元件設計時不應該透過註解進行任何的功能定義，功能定義應只通過 `ConfigProvider` 來定義； 
- 類的設計時應儘可能的不儲存狀態資料，因為這會導致這個類不能作為長生命週期的物件來提供，也無法很方便的使用依賴注入功能，這樣會在一定程度下降低效能，狀態資料應都透過 `Hyperf\Context\Context` 協程上下文來儲存；
