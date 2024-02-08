# 安装

## 服务器要求

Hyperf 对系统环境有一些要求，当您使用 Swoole 网络引擎驱动时，仅可运行于 Linux 和 Mac 环境下，但由于 Docker 虚拟化技术的发展，在 Windows 下也可以通过 Docker for Windows 来作为运行环境，通常来说 Mac 环境下，我们更推荐本地环境部署，以避免 Docker 共享磁盘缓慢导致 Hyperf 启动速度慢的问题。当您使用 Swow 网络引擎驱动时，则可在 Windows、Linux、Mac 下运行。

[hyperf/hyperf-docker](https://github.com/hyperf/hyperf-docker) 项目内已经为您准备好了各种版本的 Dockerfile ，或直接基于已经构建好的 [hyperf/hyperf](https://hub.docker.com/r/hyperf/hyperf) 镜像来运行。   

当您不想采用 Docker 来作为运行的环境基础时，也可以考虑使用 [Box](zh-cn/eco/box.md) 来作为运行的基础环境，如果您希望自行完成环境搭建，则您需要确保您的运行环境达到了以下的要求：   

 - PHP >= 8.1
 - 以下任一网络引擎
   - [Swoole PHP 扩展](https://github.com/swoole/swoole-src) >= 5.0，并关闭了 `Short Name`
   - [Swow PHP 扩展](https://github.com/swow/swow) >= 1.4
 - JSON PHP 扩展
 - Pcntl PHP 扩展（仅在 Swoole 引擎时）
 - OpenSSL PHP 扩展（如需要使用到 HTTPS）
 - PDO PHP 扩展 （如需要使用到 MySQL 客户端）
 - Redis PHP 扩展 （如需要使用到 Redis 客户端）
 - Protobuf PHP 扩展 （如需要使用到 gRPC 服务端或客户端）

## 安装 Hyperf

Hyperf 使用 [Composer](https://getcomposer.org) 来管理项目的依赖，在使用 Hyperf 之前，请确保你的运行环境已经安装好了 Composer。

### 通过 `Composer` 创建项目

我们已经为您准备好的一个骨架项目，内置了一些常用的组件及相关配置的文件及结构，是一个可以快速用于业务开发的 Web 项目基础，在安装时，您可根据您自身的需求，对组件依赖进行选择。   
执行下面的命令可以于当前所在位置创建一个 skeleton 项目

基于 Swoole 驱动：   
```
composer create-project hyperf/hyperf-skeleton 
```
基于 Swow 驱动：   
```
composer create-project hyperf/swow-skeleton 
```

> 安装过程中，对于自己不清楚的选项，请直接使用回车处理，避免因自动添加了部分监听器，但又没有正确配置时，导致服务无法启动的问题。

### Docker 下开发

假设您的本机环境并不能达到 Hyperf 的环境要求，或对于环境配置不是那么熟悉，那么您可以通过以下方法来运行及开发 Hyperf 项目：

- 启动容器

可以根据实际情况，映射到宿主机对应的目录，以下以 `/workspace/skeleton` 为例

> 如果 docker 启动时开启了 selinux-enabled 选项，容器内访问宿主机资源就会受限，所以启动容器时可以增加 --privileged -u root 选项

```shell
docker run --name hyperf \
-v /workspace/skeleton:/data/project \
-w /data/project \
-p 9501:9501 -it \
--privileged -u root \
--entrypoint /bin/sh \
hyperf/hyperf:8.1-alpine-v3.18-swoole
```

- 创建项目

```shell
composer create-project hyperf/hyperf-skeleton
```

- 启动项目

```shell
cd hyperf-skeleton
php bin/hyperf.php start
```

接下来，就可以在宿主机 `/workspace/skeleton/hyperf-skeleton` 中看到您安装好的代码了。
由于 Hyperf 是持久化的 CLI 框架，当您修改完您的代码后，通过 `CTRL + C` 终止当前启动的进程实例，并重新执行 `php bin/hyperf.php start` 启动命令即可。

## 存在兼容性问题的扩展

由于 Hyperf 基于 Swoole 协程实现，而 Swoole 4 带来的协程功能是 PHP 前所未有的，所以与不少扩展都仍存在兼容性的问题。   
以下扩展（包括但不限于）都会造成一定的兼容性问题，不能与之共用或共存：

- xhprof
- xdebug (当 PHP 版本 >= 8.1 且 Swoole 版本大于等于 5.0.2 时可用)
- blackfire
- trace
- uopz
