# 安装

## 服务器要求

Hyperf 对系统环境有一些要求，仅可运行于 Linux 和 Mac 环境下，但由于 Docker 虚拟化技术的发展，在 Windows 下也可以通过 Docker for Windows 来作为运行环境，通常来说 Mac 环境下，我们更推荐本地环境部署，以避免 Docker 共享磁盘缓慢导致 Hyperf 启动速度慢的问题。   

[hyperf\hyperf-docker](https://github.com/hyperf/hyperf-docker) 项目内已经为您准备好了各种版本的 Dockerfile ，或直接基于已经构建好的 [hyperf\hyperf](https://hub.docker.com/r/hyperf/hyperf) 镜像来运行。   

当您不想采用 Docker 来作为运行的环境基础时，您需要确保您的运行环境达到了以下的要求：   

 - PHP >= 7.2
 - Swoole PHP 扩展 >= 4.5，并关闭了 `Short Name`
 - OpenSSL PHP 扩展
 - JSON PHP 扩展
 - PDO PHP 扩展 （如需要使用到 MySQL 客户端）
 - Redis PHP 扩展 （如需要使用到 Redis 客户端）
 - Protobuf PHP 扩展 （如需要使用到 gRPC 服务端或客户端）


## 安装 Hyperf

Hyperf 使用 [Composer](https://getcomposer.org) 来管理项目的依赖，在使用 Hyperf 之前，请确保你的运行环境已经安装好了 Composer。

### 通过 `Composer` 创建项目

[hyperf/hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton) 项目是我们已经为您准备好的一个骨架项目，内置了一些常用的组件及相关配置的文件及结构，是一个可以快速用于业务开发的 Web 项目基础，在安装时，您可根据您自身的需求，对组件依赖进行选择。   
执行下面的命令可以于当前所在位置创建一个 hyperf-skeleton 项目
```
composer create-project hyperf/hyperf-skeleton 
```

### Docker 下开发

假设您的本机环境并不能达到 Hyperf 的环境要求，或对于环境配置不是那么熟悉，那么您可以通过以下方法来运行及开发 Hyperf 项目：

```
# 下载并运行 hyperf/hyperf 镜像，并将镜像内的项目目录绑定到宿主机的 /tmp/skeleton 目录
docker run -v /tmp/skeleton:/hyperf-skeleton -p 9501:9501 -it --entrypoint /bin/sh hyperf/hyperf:latest

# 镜像容器运行后，在容器内安装 Composer
wget https://github.com/composer/composer/releases/download/1.8.6/composer.phar
chmod u+x composer.phar
mv composer.phar /usr/local/bin/composer
# 将 Composer 镜像设置为阿里云镜像，加速国内下载速度
composer config -g repo.packagist composer https://mirrors.aliyun.com/composer

# 通过 Composer 安装 hyperf/hyperf-skeleton 项目
composer create-project hyperf/hyperf-skeleton

# 进入安装好的 Hyperf 项目目录
cd hyperf-skeleton
# 启动 Hyperf
php bin/hyperf.php start
```

接下来，就可以在 `/tmp/skeleton` 中看到您安装好的代码了。由于 Hyperf 是持久化的 CLI 框架，当您修改完您的代码后，通过 `CTRL + C` 终止当前启动的进程实例，并重新执行 `php bin/hyperf.php start` 启动命令即可。

## 存在兼容性问题的扩展

由于 Hyperf 基于 Swoole 协程实现，而 Swoole 4 带来的协程功能是 PHP 前所未有的，所以与不少扩展都仍存在兼容性的问题。   
以下扩展（包括但不限于）都会造成一定的兼容性问题，不能与之共用或共存：

- xhprof
- xdebug
- blackfire
- trace
- uopz
