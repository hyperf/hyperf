# 熱更新 Watcher

自從 `2.0` 版本使用了 `BetterReflection` 來收集掃描目錄內的 `語法樹` 和 `反射數據`，導致掃描速度相較 `1.1` 慢了不少。

> 首次啟動，因為沒有任何緩存，所以會比較慢，當二次啟動時，會按照文件修改時間，進行動態收集，但因為仍需要實例化 `BetterReflection`，所以啟動時間仍然比較長。

`Watcher` 組件除了解決上述啟動問題，還提供了文件修改後立馬重啟的功能。

## 安裝

```
composer require hyperf/watcher --dev
```

## 配置

### 發佈配置

```bash
php bin/hyperf.php vendor:publish hyperf/watcher
```

### 配置説明

|      配置      |      默認值      |                           備註                            |
| :------------: | :--------------: | :-------------------------------------------------------: |
|     driver     | `ScanFileDriver` |                   默認定時掃描文件驅動                    |
|      bin       |      `php`       | 用於啟動服務的腳本 例如 `php -d swoole.use_shortname=Off` |
|   watch.dir    | `app`, `config`  |                         監聽目錄                          |
|   watch.file   |      `.env`      |                         監聽文件                          |
| watch.interval |      `2000`      |                      掃描間隔(毫秒)                       |

## 支持驅動

|                 驅動                  |                備註                 |
| :-----------------------------------: | :---------------------------------: |
| Hyperf\Watcher\Driver\ScanFileDriver  |              無需擴展               |
|  Hyperf\Watcher\Driver\FswatchDriver  |          需要安裝 fswatch           |
|   Hyperf\Watcher\Driver\FindDriver    | 需要安裝 find，MAC 下需要安裝 gfind |
| Hyperf\Watcher\Driver\FindNewerDriver |            需要安裝 find            |

### `fswatch` 安裝

Mac

```bash
brew install fswatch
```

Ubuntu/Debian

```bash
apt-get install fswatch
```

其他

```bash
wget https://github.com/emcrisostomo/fswatch/releases/download/1.14.0/fswatch-1.14.0.tar.gz \
&& tar -xf fswatch-1.14.0.tar.gz \
&& cd fswatch-1.14.0/ \
&& ./configure \
&& make \
&& make install
```

## 啟動

因為目錄的關係，需要在項目根目錄中運行。

```bash
php bin/hyperf.php server:watch
```

## 不足

- 暫時 Alpine Docker 環境下，稍微有點問題，後續會完善。
- 刪除文件和修改`.env`需要手動重啟才能生效。
- vendor 中的文件需要使用 classmap 形式自動加載才能被掃描。（即執行`composer dump-autoload -o`)

