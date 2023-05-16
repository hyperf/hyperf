# Installation

## Requirements

Hyperf can only run on the Linux and MacOS system environments. However, due to the development of Docker virtualization technology, it is possible to use Windows as the system environment using Docker for Windows. If you use MacOS we recommend a local deployment to avoid the Docker shared disk causing slow startup times for Hyperf.

Various Dockerfiles have been prepared for in the [hyperf/hyperf-docker](https://github.com/hyperf/hyperf-docker) project, or you can use a prebuilt image based on [hyperf\Hyperf](https://hub.docker.com/r/hyperf/hyperf).

If you don't use Docker as the basis for your system environment, you can also consider using [Box]((en/eco/box.md)) as the basic environment for running. If you wish to set up the environment yourself, you need to make sure that your native environment meets the following requirements:

 - PHP >= 8.0
 - Any of the following network engines
   - [Swoole PHP extension]((https://github.com/swoole/swoole-src)) >= 4.5，with `swoole.use_shortname` set to `Off` in your `php.ini`
   - [Swow PHP extension](https://github.com/swow/swow)
 - JSON PHP extension
 - Pcntl PHP extension (Only on Swoole engine)
 - OpenSSL PHP extension （If you need to use the HTTPS）
 - PDO PHP extension （If you need to use the MySQL Client）
 - Redis PHP extension （If you need to use the Redis Client）
 - Protobuf PHP extension （If you need to use the gRPC Server or Client）


## Install Hyperf

Hyperf uses [Composer](https://getcomposer.org) to manage project dependencies. Before using Hyperf, make sure your operating environment has Composer installed.

### Create project via `Composer`

The project [hyperf/hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton) is a skeleton project that we have prepared for you, with built-in files for common components and related configuration. It is a foundational web project that can be quickly used to get started with professional Hyperf development. At the time of installation, you can choose component dependencies according to your own needs.
Execute the following command to create a hyperf-skeleton project at the current location

Based on Swoole engine:
```
composer create-project hyperf/hyperf-skeleton 
```

Based on Swow engine:
```
composer create-project hyperf/swow-skeleton 
```

> During the installation process, for options that you are not sure about, please directly press Enter to avoid issues where the service cannot be started due to automatic addition of some listeners without proper configuration.

### Develop in Docker

If your native environment does not meet the Hyperf system requirements, or if you are unfamiliar with  system configuration, you can run and develop the Hyperf project as follows using Docker.

- Run Container

In the following example the host will be mapped to the local directory `/workspace/skeleton`:

> If the `selinux-enabled` option is enabled when docker starts, access to host resources in the container will be restricted, so you should add the `--privileged -u root` option when starting the container.

```shell
docker run --name hyperf \
-v /workspace/skeleton:/data/project \
-p 9501:9501 -it \
--privileged -u root \
--entrypoint /bin/sh \
hyperf/hyperf:8.0-alpine-v3.15-swoole
```

- Create Project

```shell
cd /data/project
composer create-project hyperf/hyperf-skeleton
```

- Start the project

```shell
cd hyperf-skeleton
php bin/hyperf.php start
```

Next, you can see your installed project in `/workspace/skeleton/hyperf-skeleton`. Since Hyperf is a persistent CLI framework, when you have modified your code, you should terminate the running process instance with `CTRL + C` and re-execute the `php bin/hyperf.php start` startup command to restart your server and reload the code.

## Incompatible extensions

Because Hyperf is based on Swoole's unprecedented coroutine functionality many extensions are incompatible, the following (including but not limited to) extensions are currently incompatible:

- xhprof
- xdebug (It's available in PHP 8.1+ and Swoole >= 5.0.2)
- blackfire
- trace
- uopz
