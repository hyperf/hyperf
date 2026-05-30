# Installation

## Server Requirements

Hyperf has some requirements for the system environment. When using the Swoole network engine, it can only run in Linux and Mac environments. However, with the development of Docker virtualization technology, it can also run on Windows via Docker for Windows. Generally, in Mac environments, we recommend local deployment to avoid slow startup speeds caused by slow Docker shared disk access. When using the Swow network engine, it can run on Windows, Linux, and Mac.

The [hyperf/hyperf-docker](https://github.com/hyperf/hyperf-docker) project has prepared various versions of Dockerfiles for you, or you can run it directly based on the already built [hyperf/hyperf](https://hub.docker.com/r/hyperf/hyperf) image.

If you do not want to use Docker as your running environment base, you can also consider using [Box](../eco/box.md) as the base environment. If you wish to set up the environment yourself, you need to ensure that your running environment meets the following requirements:

 - PHP >= 8.1
 - Either of the following network engines:
   - [Swoole PHP extension](https://github.com/swoole/swoole-src) >= 5.0, with `Short Name` disabled
   - [Swow PHP extension](https://github.com/swow/swow) >= 1.4
 - JSON PHP extension
 - Pcntl PHP extension (only when using Swoole engine)
 - OpenSSL PHP extension (if HTTPS is needed)
 - PDO PHP extension (if MySQL client is needed)
 - Redis PHP extension (if Redis client is needed)
 - Protobuf PHP extension (if gRPC server or client is needed)

## Installing Hyperf

Hyperf uses [Composer](https://getcomposer.org) to manage project dependencies. Before using Hyperf, please ensure that Composer is installed in your running environment.

### Creating a project via `Composer`

We have prepared a skeleton project for you, which includes some commonly used components and related configuration files and structures. It is a basic Web project that can be quickly used for business development. During installation, you can select component dependencies according to your own needs.

Execute the following command to create a skeleton project in your current location:

Based on Swoole driver:
```
composer create-project hyperf/hyperf-skeleton 
```
Based on Swow driver:
```
composer create-project hyperf/swow-skeleton 
```

> During the installation process, for options you are unsure about, please just press Enter to proceed. This avoids issues where services fail to start due to automatically adding some listeners without correct configuration.

### Development under Docker

If your local environment does not meet the Hyperf environment requirements, or if you are not familiar with environment configuration, you can run and develop Hyperf projects using the following method:

- Starting a container

You can map it to the corresponding directory on the host machine according to the actual situation. The following is an example using `/workspace/skeleton`.

> If the `selinux-enabled` option is enabled when starting Docker, access to host resources from within the container will be restricted. Therefore, you can add the `--privileged -u root` options when starting the container.

```shell
docker run --name hyperf \
-v /workspace/skeleton:/data/project \
-w /data/project \
-p 9501:9501 -it \
--privileged -u root \
--entrypoint /bin/sh \
hyperf/hyperf:8.1-alpine-v3.18-swoole
```

- Creating a project

```shell
composer create-project hyperf/hyperf-skeleton
```

- Starting the project

```shell
cd hyperf-skeleton
php bin/hyperf.php start
```

Next, you will see your installed code in the host machine's `/workspace/skeleton/hyperf-skeleton`.
Since Hyperf is a persistent CLI framework, after you modify your code, terminate the current running process instance with `CTRL + C` and re-execute the `php bin/hyperf.php start` command to start it.

## Extensions with Compatibility Issues

Since Hyperf is implemented based on Swoole coroutines, and the coroutine functionality brought by Swoole 4 is unprecedented in PHP, there are still compatibility issues with many extensions.
The following extensions (but not limited to) will cause certain compatibility issues and cannot be used together or coexist with Hyperf:

- xhprof
- xdebug (available when PHP version >= 8.1 and Swoole version >= 5.0.2)
- blackfire
- trace
- uopz
