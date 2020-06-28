# Installation

## Requirements

Hyperf has some requirements for the system environment, it can only run under Linux and Mac environment, but due to the development of Docker virtualization technology, Docker for Windows can also be used as the running environment under Windows. Generally, in Mac environment, we are more A local environment deployment is recommended, to avoid the problem of slowly I/O  of shared disks, this will causing Hyperf to start up slowly. 

The various versions of Dockerfile have been prepared for you in the [hyperf\hyperf-docker](https://github.com/hyperf/hyperf-docker) project, or directly based on the already built [hyperf\ Hyperf] (https://hub.docker.com/r/hyperf/hyperf) Image to run.

When you don't want to use Docker as the basis for your running environment, you need to make sure that your operating environment meets the following requirements:  

 - PHP >= 7.2
 - Swoole PHP extension >= 4.5，and Disabled `Short Name`
 - OpenSSL PHP extension
 - JSON PHP extension
 - PDO PHP extension （If you need to use MySQL Client）
 - Redis PHP extension （If you need to use Redis Client）
 - Protobuf PHP extension （If you need to use gRPC Server of Client）


## Install Hyperf

Hyperf uses [Composer] (https://getcomposer.org) to manage project dependencies. Before using Hyperf, make sure your operating environment has Composer installed.

### Create project via `Composer`

The project [hyperf/hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton) is a skeleton project that we have prepared for you, with built-in files for common components and related configuration. And it is a Web project foundation that can be quickly used for business development. At the time of installation, you can choose component dependencies according to your own needs.
Execute the following command to create a hyperf-skeleton project at the current location

```
composer create-project hyperf/hyperf-skeleton 
```

### Develope in Docker

Assuming your native environment does not meet the Hyperf environment requirements, or maybe you are not so familiar with the environment configuration, you can run and develop the Hyperf project in the following ways:

```
# Download and run hyperf/hyperf image，and bind the directory of project with /tmp/skeleton of Host
docker run -v /tmp/skeleton:/hyperf-skeleton -p 9501:9501 -it --entrypoint /bin/sh hyperf/hyperf:latest

# After the mirror container is running, install Composer in the container
wget https://github.com/composer/composer/releases/download/1.8.6/composer.phar
chmod u+x composer.phar
mv composer.phar /usr/local/bin/composer

# Install hyperf/hyperf-skeleton project via Composer
composer create-project hyperf/hyperf-skeleton

# Cd to the installed directory
cd hyperf-skeleton
# Start Hyperf
php bin/hyperf.php start
```

Next, you can see your installed project in `/tmp/skeleton`. Since Hyperf is a persistent CLI framework, when you have modified your code, you should terminate the currently started process instance with `CTRL + C` and re-execute the `php bin/hyperf.php start` startup command to restart your server and reload the code.

## Incompatible extensions

Because Hyperf is based on the Swoole coroutine implementation, and the Swoole 4's coroutine functionality is unprecedented in PHP, there is still in-compatibility with many extensions.
The following extensions (including but not limited to) will cause certain in-compatibility issues:

- xhprof
- xdebug
- blackfire
- trace
- uopz
