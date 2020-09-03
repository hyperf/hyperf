# 简介

Hyperf 为您提供了分布式系统的外部化配置支持，默认适配了:

- 由携程开源的 [ctripcorp/apollo](https://github.com/ctripcorp/apollo)，由 [hyperf/config-apollo](https://github.com/hyperf/config-apollo) 组件提供功能支持。
- 阿里云提供的免费配置中心服务 [应用配置管理(ACM, Application Config Manager)](https://help.aliyun.com/product/59604.html)，由 [hyperf/config-aliyun-acm](https://github.com/hyperf/config-aliyun-acm) 组件提供功能支持。

## 为什么要使用配置中心？

随着业务的发展，微服务架构的升级，服务的数量、应用的配置日益增多（各种微服务、各种服务器地址、各种参数），传统的配置文件方式和数据库的方式已经可能无法满足开发人员对配置管理的要求，同时对于配置的管理可能还会牵涉到 ACL 权限管理、配置版本管理和回滚、格式验证、配置灰度发布、集群配置隔离等问题，以及：

- 安全性：配置跟随源代码保存在版本管理系统中，容易造成配置泄漏
- 时效性：修改配置，需要每台服务器每个应用修改并重启服务
- 局限性：无法支持动态调整，例如日志开关、功能开关等   

因此，我们可以通过一个配置中心以一种科学的管理方式来统一管理相关的配置。

## 安装

### Apollo

```bash
composer require hyperf/config-apollo
```

### Aliyun ACM

```bash
composer require hyperf/config-aliyun-acm
```

## 接入 Apollo 配置中心

如果您没有对配置组件进行替换使用默认的 [hyperf/config](https://github.com/hyperf/config) 组件的话，接入 Apollo 配置中心则是轻而易举，只需两步。
- 通过 Composer 将 [hyperf/config-apollo](https://github.com/hyperf/config-apollo) ，即执行命令 `composer require hyperf/config-apollo`
- 在 `config/autoload` 文件夹内增加一个 `apollo.php` 的配置文件，配置内容如下

```php
<?php
return [
    // 是否开启配置中心的接入流程，为 true 时会自动启动一个 ConfigFetcherProcess 进程用于更新配置
    'enable' => true,
    // 是否使用独立进程来拉取config，如果否则将在worker内以协程方式拉取
    'use_standalone_process' => true,
    // Apollo Server
    'server' => 'http://127.0.0.1:8080',
    // 您的 AppId
    'appid' => 'test',
    // 当前应用所在的集群
    'cluster' => 'default',
    // 当前应用需要接入的 Namespace，可配置多个
    'namespaces' => [
        'application',
    ],
    // 配置更新间隔（秒）
    'interval' => 5,
    // 严格模式，当为 false 时，拉取的配置值均为 string 类型，当为 true 时，拉取的配置值会转化为原配置值的数据类型
    'strict_mode' => false,
    // 客户端IP
    'client_ip' => current(swoole_get_local_ip()),
    // 拉取配置超时时间
    'pullTimeout' => 10,
    // 拉取配置间隔
    'interval_timeout' => 60,
];
```

## 接入 Aliyun ACM 配置中心

接入 Aliyun ACM 配置中心与 Apollo 一样都是轻而易举的，同样只需两步。
- 通过 Composer 将 [hyperf/config-aliyun-acm](https://github.com/hyperf/config-aliyun-acm) ，即执行命令 `composer require hyperf/config-aliyun-acm`
- 在 `config/autoload` 文件夹内增加一个 `aliyun_acm.php` 的配置文件，配置内容如下

```php
<?php
return [
    // 是否开启配置中心的接入流程，为 true 时会自动启动一个 ConfigFetcherProcess 进程用于更新配置
    'enable' => true,
    // 是否使用独立进程来拉取config，如果否则将在worker内以协程方式拉取
    'use_standalone_process' => true,
    // 配置更新间隔（秒）
    'interval' => 5,
    // 阿里云 ACM 断点地址，取决于您的可用区
    'endpoint' => env('ALIYUN_ACM_ENDPOINT', 'acm.aliyun.com'),
    // 当前应用需要接入的 Namespace
    'namespace' => env('ALIYUN_ACM_NAMESPACE', ''),
    // 您的配置对应的 Data ID
    'data_id' => env('ALIYUN_ACM_DATA_ID', ''),
    // 您的配置对应的 Group
    'group' => env('ALIYUN_ACM_GROUP', 'DEFAULT_GROUP'),
    // 您的阿里云账号的 Access Key
    'access_key' => env('ALIYUN_ACM_AK', ''),
    // 您的阿里云账号的 Secret Key
    'secret_key' => env('ALIYUN_ACM_SK', ''),
];
```

## 接入 Etcd 配置中心

- 安装 `Etcd 客户端`

```
composer require hyperf/etcd
```

因为 `Etcd` 分为 `v2` 和 `v3` 版本，所以根据需要选择安装

```
# Etcd v3 http client.
composer require start-point/etcd-php
# Etcd v2 http client.
composer require linkorb/etcd-php
```

- 添加 `Etcd 客户端` 配置文件 `etcd.php`

```php
<?php
return [
    'uri' => 'http://192.168.1.200:2379',
    'version' => 'v3beta',
    'options' => [
        'timeout' => 10,
    ],
];
```

- 安装 `Etcd 配置中心`

```
composer require hyperf/config-etcd
```

- 添加 `Etcd 配置中心` 配置文件 `config_etcd.php`

> mapping 为 `Etcd` 与 `Config` 的映射关系。映射中不存在的 `key`，则不会被同步到 `Config` 中。

```php
<?php
return [
    'enable' => true,
    'use_standalone_process' => true,
    'namespaces' => [
        '/test',
    ],
    'mapping' => [
        '/test/test' => 'etcd.test.test',
    ],
    'interval' => 5,
];
```

## 配置更新的作用范围

在默认的功能实现下，是由一个 `ConfigFetcherProcess` 进程根据配置的 `interval` 来向 Apollo 拉取对应 `namespace` 的配置，并通过 IPC 通讯将拉取到的新配置传递到各个 Worker 中，并更新到 `Hyperf\Contract\ConfigInterface` 对应的对象内。   
需要注意的是，更新的配置只会更新 `Config` 对象，故仅限应用层或业务层的配置，不涉及框架层的配置改动，因为框架层的配置改动需要重启服务，如果您有这样的需求，也可以通过自行实现 `ConfigFetcherProcess` 来达到目的。
