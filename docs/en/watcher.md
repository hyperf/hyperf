# Hot Update Watcher

> The first time you start it, it will be relatively slow because there is no cache. The second time you start it, it will be dynamically collected according to the file modification time, so the startup time will still be relatively long.

In addition to solving the above startup problems, the `Watcher` component also provides the function of restarting immediately after file modification.

## Installation

```bash
composer require hyperf/watcher --dev
```

## Configuration

### Publish configuration

```bash
php bin/hyperf.php vendor:publish hyperf/watcher
```

### Configuration description

| Configuration | Default Value | Description |
| :------------: | :--------------: | :-------------------------------------------------------: |
| driver | `ScanFileDriver` | Default scheduled file scanning driver |
| bin | `PHP_BINARY` | Script to start the service, e.g., `php -d swoole.use_shortname=Off` |
| watch.dir | `app`, `config` | Watched directories |
| watch.file | `.env` | Watched files |
| watch.interval | `2000` | Scanning interval (milliseconds) |
| ext | `.php`, `.env` | File extensions under the watched directory |

## Supported Drivers

| Driver | Description |
| :-----------------------------------: | :---------------------------------: |
| Hyperf\Watcher\Driver\ScanFileDriver | No extension required |
| Hyperf\Watcher\Driver\FswatchDriver | fswatch needs to be installed |
| Hyperf\Watcher\Driver\FindDriver | find needs to be installed, gfind needs to be installed on MAC |
| Hyperf\Watcher\Driver\FindNewerDriver | find needs to be installed |

### `fswatch` Installation

Mac

```bash
brew install fswatch
```

Ubuntu/Debian

```bash
apt-get install fswatch
```

Other

```bash
wget https://github.com/emcrisostomo/fswatch/releases/download/1.14.0/fswatch-1.14.0.tar.gz \
&& tar -xf fswatch-1.14.0.tar.gz \
&& cd fswatch-1.14.0/ \
&& ./configure \
&& make \
&& make install
```

## Startup

Due to directory reasons, it needs to be run in the project root directory.

```bash
php bin/hyperf.php server:watch
```

## Limitations

- Currently, there are some minor issues in the Alpine Docker environment, which will be improved later.
- Deleting files and modifying `.env` require a manual restart to take effect.
