# 安装

## 服务器要求

Hyperf 对系统环境有一些要求，仅可运行于 Linux 和 Mac 环境下，但由于 Docker 虚拟化技术的发展，在 Windows 下也可以通过 Docker for Windows 来作为运行环境。   
[hyperf\hyperf](https://github.com/hyperf-cloud/hyperf) 项目内已经为您准备好了一个 Dockerfile ，或直接基于已经构建好的 hyperf\hyperf 镜像来运行。   

当您不想采用 Docker 来作为运行的环境基础时，你需要确保您的运行环境达到了以下的要求：   

 - PHP >= 7.2
 - Swoole PHP 扩展 >= 4.3.1
 - OpenSSL PHP 扩展
 - JSON PHP 扩展
 - PDO PHP 扩展 （如需要使用到 MySQL）
 - Redis PHP 扩展 （如需要使用到 Redis）


## 安装 Hyperf

Hyperf 使用 Composer 来管理项目的依赖，在使用 Hyperf 之前，请确保你的运行环境已经安装好了 Composer。

### 通过 `composer create` 命令创建 [Skeleton](https://github.com/hyperf-cloud/hyperf-skeleton) 项目
~~~
composer create hyperf/hyperf-skeleton 
~~~