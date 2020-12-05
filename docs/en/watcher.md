# Code Update Watcher

Since version `2.0` uses `BetterReflection` to collect the `syntax tree` and `reflection data` in the scanned directory, the scanning speed is much slower than version `1.1`.

> The first application startup will be slower because there is no cache. The startup process should be sped up by subsequent dynamic startups, but because `BetterReflection` needs to be instantiated, the startup time is still relatively long.


In addition to solving the above startup problems, the `Watcher` component also handles restarting the application immediately after file modification.

## Installation

```
composer require hyperf/watcher --dev
```

## Configuration

### Publish configuration

```bash
php bin/hyperf.php vendor:publish hyperf/watcher
```

### Configuration instructions

|      Name      |      Default     |                                      Description                                     |
|:--------------:|:----------------:|:------------------------------------------------------------------------------------:|
|     driver     | `ScanFileDriver` |                           The default polling file watcher                           |
|       bin      |       `php`      | The script used to start the service, for example: `php -d swoole.use_shortname=Off` |
|    watch.dir   |  `app`, `config` |                                  Watched directories                                 |
|   watch.file   |      `.env`      |                                     Wached files                                     |
| watch.interval |      `2000`      |                                 Polling interval (ms)                                |

## Driver support

|                 Driver               |                Notes                |
| :----------------------------------: | :---------------------------------: |
| Hyperf\Watcher\Driver\ScanFileDriver |        no extension required        |
| Hyperf\Watcher\Driver\FswatchDriver  |          requires fswatch           |
|   Hyperf\Watcher\Driver\FindDriver   |  requires find，MAC requires gfind  |

### `fswatch` Installation
Mac

```bash
brew install fswatch
```

Other systems

```bash
wget https://github.com/emcrisostomo/fswatch/releases/download/1.14.0/fswatch-1.14.0.tar.gz \
&& tar -xf fswatch-1.14.0.tar.gz \
&& cd fswatch-1.14.0/ \
&& ./configure \
&& make \
&& make install
```

## Startup

Because of the directory structure, the command has to be run in the project
root directory.

```bash
php bin/hyperf.php server:watch
```

## Problems

- For the time being, there is a slight problem in the Alpine Docker environment, which will be improved in the future.
- Deletion of files and modification of .env require a manual restart to take
  effect.
-  Files in the vendor need to be automatically loaded in the form of classmap before they can be scanned. (I.e. execute composer dump-autoload -o)
