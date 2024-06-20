# 熱更新 Watcher

> 首次啟動，因為沒有任何快取，所以會比較慢，當二次啟動時，會按照檔案修改時間，進行動態收集，所以啟動時間仍然比較長。

`Watcher` 元件除了解決上述啟動問題，還提供了檔案修改後立馬重啟的功能。

## 安裝

```bash
composer require hyperf/watcher --dev
```

## 配置

### 釋出配置

```bash
php bin/hyperf.php vendor:publish hyperf/watcher
```

### 配置說明

|      配置      |      預設值      |                           備註                            |
| :------------: | :--------------: | :-------------------------------------------------------: |
|     driver     | `ScanFileDriver` |                   預設定時掃描檔案驅動                    |
|      bin       |   `PHP_BINARY`   | 用於啟動服務的指令碼 例如 `php -d swoole.use_shortname=Off` |
|   watch.dir    | `app`, `config`  |                         監聽目錄                          |
|   watch.file   |      `.env`      |                         監聽檔案                          |
| watch.interval |      `2000`      |                      掃描間隔(毫秒)                       |
|      ext       |  `.php`, `.env`  |                  監聽目錄下的副檔名                   |

## 支援驅動

|                 驅動                  |                備註                 |
| :-----------------------------------: | :---------------------------------: |
| Hyperf\Watcher\Driver\ScanFileDriver  |              無需擴充套件               |
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

因為目錄的關係，需要在專案根目錄中執行。

```bash
php bin/hyperf.php server:watch
```

## 不足

- 暫時 Alpine Docker 環境下，稍微有點問題，後續會完善。
- 刪除檔案和修改`.env`需要手動重啟才能生效。
