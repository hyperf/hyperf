# 热更新 Watcher

> 首次启动，因为没有任何缓存，所以会比较慢，当二次启动时，会按照文件修改时间，进行动态收集，所以启动时间仍然比较长。

`Watcher` 组件除了解决上述启动问题，还提供了文件修改后立马重启的功能。

## 安装

```bash
composer require hyperf/watcher --dev
```

## 配置

### 发布配置

```bash
php bin/hyperf.php vendor:publish hyperf/watcher
```

### 配置说明

|      配置      |      默认值      |                           备注                            |
| :------------: | :--------------: | :-------------------------------------------------------: |
|     driver     | `ScanFileDriver` |                   默认定时扫描文件驱动                    |
|      bin       |   `PHP_BINARY`   | 用于启动服务的脚本 例如 `php -d swoole.use_shortname=Off` |
|   watch.dir    | `app`, `config`  |                         监听目录                          |
|   watch.file   |      `.env`      |                         监听文件                          |
| watch.interval |      `2000`      |                      扫描间隔(毫秒)                       |
|      ext       |  `.php`, `.env`  |                  监听目录下的文件扩展名                   |

## 支持驱动

|                 驱动                  |                备注                 |
| :-----------------------------------: | :---------------------------------: |
| Hyperf\Watcher\Driver\ScanFileDriver  |              无需扩展               |
|  Hyperf\Watcher\Driver\FswatchDriver  |          需要安装 fswatch           |
|   Hyperf\Watcher\Driver\FindDriver    | 需要安装 find，MAC 下需要安装 gfind |
| Hyperf\Watcher\Driver\FindNewerDriver |            需要安装 find            |

### `fswatch` 安装

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

## 启动

因为目录的关系，需要在项目根目录中运行。

```bash
php bin/hyperf.php server:watch
```

## 不足

- 暂时 Alpine Docker 环境下，稍微有点问题，后续会完善。
- 删除文件和修改`.env`需要手动重启才能生效。
