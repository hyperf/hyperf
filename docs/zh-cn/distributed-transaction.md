# 分布式事务

[dtm-client](https://github.com/dtm-php/dtm-client) 是由 Hyperf 团队开发并维护的 DTM 分布式事务客户端组件，配合 DTM-Server 可以实现分布式事务的管理，稳定可用于生产环境。   
[seata/seata-php](https://github.com/seata/seata-php) 是由 Hyperf 团队开发并贡献给 Seata 开源社区的 Seata PHP 客户端组件，配合 Seata-Server 可以实现分布式事务的管理，目前仍在开发迭代中，尚未能用于生产环境，希望大家能够共同参与进来加速孵化。

# DTM-Client 介绍

[dtm/dtm-client](https://packagist.org/packages/dtm/dtm-client) 是分布式事务管理器 [DTM](https://github.com/dtm-labs/dtm) 的 PHP 客户端，已支持 TCC 模式、Saga、XA、二阶段消息模式的分布式事务模式，并分别实现了与 DTM Server 以 HTTP 协议或 gRPC 协议通讯，该客户端可安全运行于 PHP-FPM 和 Swoole 协程环境中，更是对 [Hyperf](https://github.com/hyperf/hyperf) 做了更加易用的功能支持。

# 关于 DTM

DTM 是一款基于 Go 语言实现的开源分布式事务管理器，提供跨语言，跨存储引擎组合事务的强大功能。DTM 优雅的解决了幂等、空补偿、悬挂等分布式事务难题，也提供了简单易用、高性能、易水平扩展的分布式事务解决方案。

## 亮点

* 极易上手
    - 零配置启动服务，提供非常简单的 HTTP 接口，极大降低上手分布式事务的难度
* 跨语言
    - 可适合多语言栈的公司使用。方便 Go、Python、PHP、NodeJs、Ruby、C# 等各类语言使用。
* 使用简单
    - 开发者不再担心悬挂、空补偿、幂等各类问题，首创子事务屏障技术代为处理
* 易部署、易扩展
    - 仅依赖 MySQL/Redis，部署简单，易集群化，易水平扩展
* 多种分布式事务协议支持
    - TCC、SAGA、XA、二阶段消息，一站式解决多种分布式事务问题

## 对比

在非 Java 语言下，暂未看到除 DTM 之外的成熟的分布式事务管理器，因此这里将 DTM 和 Java 中最成熟的开源项目 Seata 做对比：

|  特性| DTM |                                              SEATA                                               |备注|
|:-----:|:----:|:------------------------------------------------------------------------------------------------:|:----:|
|[支持语言](https://dtm.pub/other/opensource.html#lang) |<span style="color:green">Go、C#、Java、Python、PHP...</span>|                            <span style="color:orange">Java、Go</span>                             |DTM 可轻松接入一门新语言|
|[存储引擎](https://dtm.pub/other/opensource.html#store) |<span style="color:green"> 支持数据库、Redis、Mongo 等 </span>|                              <span style="color:orange"> 数据库 </span>                               ||
|[异常处理](https://dtm.pub/other/opensource.html#exception)| <span style="color:green"> 子事务屏障自动处理 </span>|                              <span style="color:orange"> 手动处理 </span>                              |DTM 解决了幂等、悬挂、空补偿|
|[SAGA 事务](https://dtm.pub/other/opensource.html#saga) |<span style="color:green"> 极简易用 </span> |                             <span style="color:orange"> 复杂状态机 </span>                              ||
|[二阶段消息](https://dtm.pub/other/opensource.html#msg)|<span style="color:green">✓</span>|                                 <span style="color:red">✗</span>                                 |最简消息最终一致性架构|
|[TCC 事务](https://dtm.pub/other/opensource.html#tcc)| <span style="color:green">✓</span>|                                <span style="color:green">✓</span>                                ||
|[XA 事务](https://dtm.pub/other/opensource.html#xa)|<span style="color:green">✓</span>|                                <span style="color:green">✓</span>                                ||
|[AT 事务](https://dtm.pub/other/opensource.html#at)|<span style="color:orange"> 建议使用 XA</span>|                                <span style="color:green">✓</span>                                |AT 与 XA 类似，但有脏回滚|
|[单服务多数据源](https://dtm.pub/other/opensource.html#multidb)|<span style="color:green">✓</span>|                                 <span style="color:red">✗</span>                                 ||
|[通信协议](https://dtm.pub/other/opensource.html#protocol)|HTTP、gRPC|                                             Dubbo 等协议                                             |DTM 对云原生更加友好|
|[star 数量](https://dtm.pub/other/opensource.html#star)|<img src="https://img.shields.io/github/stars/dtm-labs/dtm.svg?style=social" alt="github stars"/>| <img src="https://img.shields.io/github/stars/seata/seata.svg?style=social" alt="github stars"/> |DTM 从 2021-06-04 发布 0.1 版本，发展飞快|

从上面对比的特性来看，DTM 在许多方面都具备很大的优势。如果考虑多语言支持、多存储引擎支持，那么 DTM 毫无疑问是您的首选.

# 安装

通过 Composer 可以非常方便的安装 dtm-client

```bash
composer require dtm/dtm-client
```

* 使用时别忘了启动 DTM Server 哦

# 配置

## 配置文件

如果您是在 Hyperf 框架中使用，在安装组件后，可通过下面的 `vendor:publish` 命令一件发布配置文件于 `./config/autoload/dtm.php`

```bash
php bin/hyperf.php vendor:publish dtm/dtm-client
```

如果您是在非 Hyperf 框架中使用，可复制 `./vendor/dtm/dtm-client/publish/dtm.php` 文件到对应的配置目录中。

```php
use DtmClient\Constants\Protocol;
use DtmClient\Constants\DbType;

return [
    // 客户端与 DTM Server 通讯的协议，支持 Protocol::HTTP 和 Protocol::GRPC 两种
    'protocol' => Protocol::HTTP,
    // DTM Server 的地址
    'server' => '127.0.0.1',
    // DTM Server 的端口
    'port' => [
        'http' => 36789,
        'grpc' => 36790,
    ],
    // 子事务屏障配置
    'barrier' => [
        // DB 模式下的子事务屏障配置
        'db' => [
            'type' => DbType::MySQL
        ],
        // Redis 模式下的子事务屏障配置
        'redis' => [
            // 子事务屏障记录的超时时间
            'expire_seconds' => 7 * 86400,
        ],
        // 非 Hyperf 框架下应用子事务屏障的类
        'apply' => [],
    ],
    // HTTP 协议下 Guzzle 客户端的通用配置
    'guzzle' => [
        'options' => [],
    ],
];
```

## 配置中间件

在使用之前，需要配置 `DtmClient\Middleware\DtmMiddleware` 中间件作为 Server 的全局中间件，该中间件支持 PSR-15 规范，可适用于各个支持该规范的的框架。   
在 Hyperf 中的中间件配置可参考 [Hyperf 文档 - 中间件](https://www.hyperf.wiki/2.2/#/zh-cn/middleware/middleware) 一章。

# 使用

dtm-client 的使用非常简单，我们提供了一个示例项目 [dtm-php/dtm-sample](https://github.com/dtm-php/dtm-sample) 来帮助大家更好的理解和调试。   
在使用该组件之前，也强烈建议您先阅读 [DTM 官方文档](https://dtm.pub/)，以做更详细的了解。

## TCC 模式

TCC 模式是一种非常流行的柔性事务解决方案，由 Try-Confirm-Cancel 三个单词的首字母缩写分别组成 TCC 的概念，最早是由 Pat Helland 于 2007 年发表的一篇名为《Life beyond Distributed Transactions:an Apostate’s Opinion》的论文中提出。

### TCC 的 3 个阶段

Try 阶段：尝试执行，完成所有业务检查（一致性）, 预留必须业务资源（准隔离性）  
Confirm 阶段：如果所有分支的 Try 都成功了，则走到 Confirm 阶段。Confirm 真正执行业务，不作任何业务检查，只使用 Try 阶段预留的业务资源  
Cancel 阶段：如果所有分支的 Try 有一个失败了，则走到 Cancel 阶段。Cancel 释放 Try 阶段预留的业务资源。

如果我们要进行一个类似于银行跨行转账的业务，转出（TransOut）和转入（TransIn）分别在不同的微服务里，一个成功完成的 TCC 事务典型的时序图如下：

<img src="https://dtm.pub/assets/tcc_normal.dea14fb3.jpg" height=600 />

### 代码示例

以下展示在 Hyperf 框架中的使用方法，其它框架类似

```php
<?php
namespace App\Controller;

use DtmClient\TCC;
use DtmClient\TransContext;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Throwable;

#[Controller(prefix: '/tcc')]
class TccController
{

    protected string $serviceUri = 'http://127.0.0.1:9501';

    #[Inject]
    protected TCC $tcc;

    #[GetMapping(path: 'successCase')]
    public function successCase()
    {
        try {
            
            $this->tcc->globalTransaction(function (TCC $tcc) {
                // 创建子事务 A 的调用数据
                $tcc->callBranch(
                    // 调用 Try 方法的参数
                    ['amount' => 30],
                    // Try 方法的 URL
                    $this->serviceUri . '/tcc/transA/try',
                    // Confirm 方法的 URL
                    $this->serviceUri . '/tcc/transA/confirm',
                    // Cancel 方法的 URL
                    $this->serviceUri . '/tcc/transA/cancel'
                );
                // 创建子事务 B 的调用数据，以此类推
                $tcc->callBranch(
                    ['amount' => 30],
                    $this->serviceUri . '/tcc/transB/try',
                    $this->serviceUri . '/tcc/transB/confirm',
                    $this->serviceUri . '/tcc/transB/cancel'
                );
            });
        } catch (Throwable $e) {
            var_dump($e->getMessage(), $e->getTraceAsString());
        }
        // 通过 TransContext::getGid() 获得 全局事务ID 并返回
        return TransContext::getGid();
    }
}
```

## Saga 模式

Saga 模式是分布式事务领域最有名气的解决方案之一，也非常流行于各大系统中，最初出现在 1987 年 由 Hector Garcaa-Molrna & Kenneth Salem 发表的论文 [SAGAS](https://www.cs.cornell.edu/andru/cs711/2002fa/reading/sagas.pdf) 里。

Saga 是一种最终一致性事务，也是一种柔性事务，又被叫做 长时间运行的事务（Long-running-transaction），Saga 是由一系列的本地事务构成。每一个本地事务在更新完数据库之后，会发布一条消息或者一个事件来触发 Saga 全局事务中的下一个本地事务的执行。如果一个本地事务因为某些业务规则无法满足而失败，Saga 会执行在这个失败的事务之前成功提交的所有事务的补偿操作。所以 Saga 模式在对比 TCC 模式时，因缺少了资源预留的步骤，往往在实现回滚逻辑时会变得更麻烦。

### Saga 子事务拆分

比如我们要进行一个类似于银行跨行转账的业务，将 A 账户中的 30 元转到 B 账户，根据 Saga 事务的原理，我们将整个全局事务，拆分为以下服务：
- 转出（TransOut）服务，这里将会进行操作 A 账户扣减 30 元
- 转出补偿（TransOutCompensate）服务，回滚上面的转出操作，即 A 账户增加 30 元
- 转入（TransIn）服务，这里将会进行 B  账户增加 30 元
- 转入补偿（TransInCompensate）服务，回滚上面的转入操作，即 B 账户减少 30 元

整个事务的逻辑是：

执行转出成功 => 执行转入成功 => 全局事务完成

如果在中间发生错误，例如转入 B 账户发生错误，则会调用已执行分支的补偿操作，即：

执行转出成功 => 执行转入失败 => 执行转入补偿成功 => 执行转出补偿成功 => 全局事务回滚完成

下面是一个成功完成的 SAGA 事务典型的时序图：

<img src="https://dtm.pub/assets/saga_normal.a2849672.jpg" height=428 />

### 代码示例

以下展示在 Hyperf 框架中的使用方法，其它框架类似

```php
namespace App\Controller;

use DtmClient\Saga;
use DtmClient\TransContext;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;

#[Controller(prefix: '/saga')]
class SagaController
{

    protected string $serviceUri = 'http://127.0.0.1:9501';
    
    #[Inject]
    protected Saga $saga;

    #[GetMapping(path: 'successCase')]
    public function successCase(): string
    {
        $payload = ['amount' => 50];
        // 初始化 Saga 事务
        $this->saga->init();
        // 增加转出子事务
        $this->saga->add(
            $this->serviceUri . '/saga/transOut', 
            $this->serviceUri . '/saga/transOutCompensate', 
            $payload
        );
        // 增加转入子事务
        $this->saga->add(
            $this->serviceUri . '/saga/transIn', 
            $this->serviceUri . '/saga/transInCompensate', 
            $payload
        );
        // 提交 Saga 事务
        $this->saga->submit();
        // 通过 TransContext::getGid() 获得 全局事务ID 并返回
        return TransContext::getGid();
    }
}
```

## XA 模式
XA 是由 X /Open 组织提出的分布式事务的规范，XA 规范主要定义了(全局)事务管理器(TM)和(局部)资源管理器(RM)之间的接口。本地的数据库如 mysql 在 XA 中扮演的是 RM 角色

XA 一共分为两阶段：

第一阶段（prepare）：即所有的参与者 RM 准备执行事务并锁住需要的资源。参与者 ready 时，向 TM 报告已准备就绪。 第二阶段 (commit/rollback)：当事务管理者(TM)确认所有参与者(RM)都 ready 后，向所有参与者发送 commit 命令。

目前主流的数据库基本都支持 XA 事务，包括 mysql、oracle、sqlserver、postgre

下面是一个成功完成的 XA 事物典型的时序图

<img src="https://dtm.pub/assets/xa_normal.5a0ce600.jpg" height=600/>

### 代码示例

以下展示在 Hyperf 框架中的使用方法，其它框架类似
```php
<?php

namespace App\Controller;

use App\Grpc\GrpcClient;
use DtmClient\DbTransaction\DBTransactionInterface;
use DtmClient\TransContext;
use DtmClient\XA;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface;

#[Controller(prefix: '/xa')]
class XAController
{

    private GrpcClient $grpcClient;

    protected string $serviceUri = 'http://127.0.0.1:9502';

    public function __construct(
        private XA $xa,
        protected ConfigInterface $config,
    ) {
        $server = $this->config->get('dtm.server', '127.0.0.1');
        $port = $this->config->get('dtm.port.grpc', 36790);
        $hostname = $server . ':' . $port;
        $this->grpcClient = new GrpcClient($hostname);
    }


    #[GetMapping(path: 'successCase')]
    public function successCase(): string
    {
        $payload = ['amount' => 50];
        // 开启Xa 全局事物
        $gid = $this->xa->generateGid();
        $this->xa->globalTransaction($gid, function () use ($payload) {
            // 调用子事物接口
            $respone = $this->xa->callBranch($this->serviceUri . '/xa/api/transIn', $payload);
            // XA http模式下获取子事物返回结构
            /* @var ResponseInterface $respone */
            $respone->getBody()->getContents();
            // 调用子事物接口
            $payload = ['amount' => 10];
            $this->xa->callBranch($this->serviceUri . '/xa/api/transOut', $payload);
        });
        // 通过 TransContext::getGid() 获得 全局事务ID 并返回
        return TransContext::getGid();
    }

    #[RequestMapping(methods: ["GET", "POST", "PUT"], path: 'api/transIn')]
    public function transIn(RequestInterface $request): array
    {
        $content = $request->post('amount');
        $amount = $content['amount'] ?? 50;
        // 模拟分布式系统下transIn方法
        $this->xa->localTransaction(function (DBTransactionInterface $dbTransaction) use ($amount) {
            // 请使用 DBTransactionInterface 处理本地 Mysql 事物
            $dbTransaction->xaExecute('UPDATE `order` set `amount` = `amount` + ? where id = 1', [$amount]);
        });

        return ['status' => 0, 'message' => 'ok'];
    }

    /**
     * @param RequestInterface $request
     * @return array
     */
    #[RequestMapping(methods: ["GET", "POST", "PUT"], path: 'api/transOut')]
    public function transOut(RequestInterface $request): array
    {
        $content = $request->post('amount');
        $amount = $content['amount'] ?? 10;
        // 模拟分布式系统下transOut方法
        $this->xa->localTransaction(function (DBTransactionInterface $dbTransaction) use ($amount) {
            // 请使用 DBTransactionInterface 处理本地 Mysql 事物
            $dbTransaction->xaExecute('UPDATE `order` set `amount` = `amount` - ? where id = 2', [$amount]);
        });

        return ['status' => 0, 'message' => 'ok'];
    }
}

```
上面的代码首先注册了一个全局 XA 事务，然后添加了两个子事务 transIn、transOut。子事务全部执行成功之后，提交给 dtm。dtm 收到提交的 xa 全局事务后，会调用所有子事务的 xa commit，完成整个 xa 事务。
