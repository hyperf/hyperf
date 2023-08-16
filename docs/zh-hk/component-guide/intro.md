# 指南前言

為了幫助開發者更好的為 Hyperf 開發組件，共建生態，我們提供了本指南用於指導開發者進行組件開發，在閲讀本指南前，需要您對 Hyperf 的文檔進行了 **全面** 的閲讀，特別是 [協程](zh-hk/coroutine.md) 和 [依賴注入](zh-hk/di.md) 章節，如果對 Hyperf 的基礎組件缺少充分的理解，可能會導致開發時出現錯誤。

# 組件開發的目的

在傳統的 PHP-FPM 架構下的開發，通常在我們需要藉助第三方庫來解決我們的需求時，都會通過 Composer 來直接引入一個對應的 `庫(Library)`，但是在 Hyperf 下，由於 `持久化應用` 和 `協程` 這兩個特性，導致了應用的生命週期和模式存在一些差異，所以並不是所有的 `庫(Library)` 都能在 Hyperf 裏直接使用，當然，一些設計優秀的 `庫(Library)` 也是可以被直接使用的。通讀本指南，便可知道如何甄別一些 `庫(Library)` 是否能直接用於項目內，不能的話該進行如何的改動。

# 組件開發準備工作

這裏所指的開發準備工作，除了 Hyperf 的基礎運行條件外，這裏關注的更多是如何更加便捷的組織代碼的結構以便於組件的開發工作，注意以下方式可能會由於 *軟連接無法跳轉的問題* 而並不適用於 Windows for Docker 下的開發環境。   
在代碼組織上，我們建議在同一個目錄下 Clone [hyperf/hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton) 項目骨架和 [hyperf/hyperf](https://github.com/hyperf/hyperf) 項目組件庫兩個項目。進行下面的操作並呈以下結構：

```bash
// 安裝 skeleton，並配置完成
composer create-project hyperf/hyperf-skeleton 

// 克隆 hyperf 組件庫項目，這裏記得要替換 hyperf 為您的 Github ID，也就是克隆您所 Fork 的項目
git clone git@github.com:hyperf/hyperf.git
```

呈以下結構：

```
.
├── hyperf
│   ├── bin
│   └── src
└── hyperf-skeleton
    ├── app
    ├── bin
    ├── config
    ├── runtime
    ├── test
    └── vendor
```

這樣做的目的是為了讓 `hyperf-skeleton` 項目可以直接通過 `path` 來源的形式，讓 Composer 直接通過 `hyperf` 文件夾內的項目作為依賴項被加載到 `hyperf-skelton`  項目的 `vendor` 目錄中，我們對 `hyperf-skelton` 內的 `composer.json` 文件增加一個 `repositories` 項，如下：

```json
{
    "repositories": {
        "hyperf": {
            "type": "path",
            "url": "../hyperf/src/*"
        }
    }
}
```
然後再在 `hyperf-skeleton` 項目內刪除 `composer.lock` 文件和 `vendor` 文件夾，再執行 `composer update` 讓依賴重新更新，命令如下：

```bash
cd hyperf-skeleton
rm -rf composer.lock && rm -rf vendor && composer update
```
   
最終使 `hyperf-skeleton/vendor/hyperf` 文件夾內的項目文件夾全部通過 `軟連接(softlink)` 連接到 `hyperf` 文件夾內。我們可以通過 `ls -l` 命令來驗證 `軟連接(softlink)` 是否已經建立成功：

```bash
cd vendor/hyperf/
ls -l
```

當我們看到類似下面這樣的連接關係，即表明 `軟連接(softlink)` 建立成功了：

```
cache -> ../../../hyperf/src/cache
command -> ../../../hyperf/src/command
config -> ../../../hyperf/src/config
contract -> ../../../hyperf/src/contract
database -> ../../../hyperf/src/database
db-connection -> ../../../hyperf/src/db-connection
devtool -> ../../../hyperf/src/devtool
di -> ../../../hyperf/src/di
dispatcher -> ../../../hyperf/src/dispatcher
event -> ../../../hyperf/src/event
exception-handler -> ../../../hyperf/src/exception-handler
framework -> ../../../hyperf/src/framework
guzzle -> ../../../hyperf/src/guzzle
http-message -> ../../../hyperf/src/http-message
http-server -> ../../../hyperf/src/http-server
logger -> ../../../hyperf/src/logger
memory -> ../../../hyperf/src/memory
paginator -> ../../../hyperf/src/paginator
pool -> ../../../hyperf/src/pool
process -> ../../../hyperf/src/process
redis -> ../../../hyperf/src/redis
server -> ../../../hyperf/src/server
testing -> ../../../hyperf/src/testing
utils -> ../../../hyperf/src/utils
```

此時，我們便可達到在 IDE 內直接對 `vendor/hyperf` 內的文件進行修改，而修改的卻是 `hyperf` 內的代碼的目的，這樣最終我們便可直接對 `hyperf` 項目內進行 `commit`，然後向主幹提交 `Pull Request(PR)` 了。
