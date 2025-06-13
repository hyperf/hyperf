# Watcher (Hot Reload)

Since version `2.0` uses `BetterReflection` to collect the `abstract syntax tree (AST)` and `reflection data`, the scanning speed is much slower than version `1.1`.

> The first startup of application will be slower because there is no scan cache exists. Subsequent startup speeds will be improved, but because `BetterReflection` needs to be instantiated, the startup time is still relatively long.


In addition to solving the above startup problems, the `Watcher` component also handles restarting the application immediately after file modification.

> This component is only suitable for development environment, please use it with caution in production environment.

## Installation

```bash
composer require hyperf/watcher --dev
```

## Configuration

### Publish configuration

```bash
php bin/hyperf.php vendor:publish hyperf/watcher
```

### Configuration instructions

|      Name      |     Default      |                                     Description                                      |
| :------------: | :--------------: | :----------------------------------------------------------------------------------: |
|     driver     | `ScanFileDriver` |                           The default polling file watcher                           |
|      bin       |   `PHP_BINARY`   | The script used to start the service, for example: `php -d swoole.use_shortname=Off` |
|   watch.dir    | `app`, `config`  |                                 Watched directories                                  |
|   watch.file   |      `.env`      |                                     Wached files                                     |
| watch.interval |      `2000`      |                                Polling interval (ms)                                 |
|      ext       |  `.php`, `.env`  |                      File extension in the listening directory                       |

## Driver support

|                Driver                 |               Notes               |
| :-----------------------------------: | :-------------------------------: |
| Hyperf\Watcher\Driver\ScanFileDriver  |       no extension required       |
|  Hyperf\Watcher\Driver\FswatchDriver  |         requires fswatch          |
|   Hyperf\Watcher\Driver\FindDriver    | requires find, MAC requires gfind |
| Hyperf\Watcher\Driver\FindNewerDriver |           requires find           |

### `fswatch` Installation
Mac:

```bash
brew install fswatch
```

Ubuntu/Debian

```bash
apt-get install fswatch
```

Linux:

```bash
wget https://github.com/emcrisostomo/fswatch/releases/download/1.14.0/fswatch-1.14.0.tar.gz \
&& tar -xf fswatch-1.14.0.tar.gz \
&& cd fswatch-1.14.0/ \
&& ./configure \
&& make \
&& make install
```

## Startup

Because of the directory structure, the start command has to be run in the root directory of project.

```bash
php bin/hyperf.php server:watch
```

## Startup with docker 
When configuring a file watcher for hot-reloading in Docker, specify the entry point in the Dockerfile as follows:

```bash
ENTRYPOINT ["php", "/opt/www/bin/hyperf.php", "server:watch"]
```

## Problems

- For now, there is a slight problem in the Alpine Docker environment, which will be improved in the future version.
- Deletion of files and modification of `.env` require a manual restart to take effect.
