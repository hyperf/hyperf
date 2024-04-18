# 熱更新 Watcher

> 首次啓動，因為沒有任何緩存，所以會比較慢，當二次啓動時，會按照文件修改時間，進行動態收集，所以啓動時間仍然比較長。

`Watcher` 組件除了解決上述啓動問題，還提供了文件修改後立馬重啓的功能。

## 安裝

```bash
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
|      bin       |   `PHP_BINARY`   | 用於啓動服務的腳本 例如 `php -d swoole.use_shortname=Off` |
|   watch.dir    | `app`, `config`  |                         監聽目錄                          |
|   watch.file   |      `.env`      |                         監聽文件                          |
| watch.interval |      `2000`      |                      掃描間隔(毫秒)                       |
|      ext       |  `.php`, `.env`  |                  監聽目錄下的文件擴展名                   |

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

## 啓動

因為目錄的關係，需要在項目根目錄中運行。

```bash
php bin/hyperf.php server:watch
```

## 不足

- 暫時 Alpine Docker 環境下，稍微有點問題，後續會完善。
- 刪除文件和修改`.env`需要手動重啓才能生效。
