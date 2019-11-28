# 版本更新记录

# v1.1.7 - 2019-11-21

## 新增

- [#860](https://github.com/hyperf/hyperf/pull/860) 新增 [hyperf/retry](https://github.com/hyperf/retry) 组件；
- [#952](https://github.com/hyperf/hyperf/pull/952) 新增 ThinkTemplate 视图引擎支持；
- [#973](https://github.com/hyperf/hyperf/pull/973) 新增 JSON RPC 在 TCP 协议下的连接池支持，通过 `Hyperf\JsonRpc\JsonRpcPoolTransporter` 来使用连接池版本；
- [#976](https://github.com/hyperf/hyperf/pull/976) 为 `hyperf/amqp` 组件新增  `close_on_destruct` 选项参数，用来控制代码在执行析构函数时是否主动去关闭连接；

## 变更

- [#944](https://github.com/hyperf/hyperf/pull/944) 将组件内所有使用 `@Listener` 和 `@Process` 注解来注册的改成通过 `ConfigProvider`来注册；
- [#977](https://github.com/hyperf/hyperf/pull/977) 调整 `init-proxy.sh` 命令的行为，改成只删除 `runtime/container` 目录；

## 修复

- [#955](https://github.com/hyperf/hyperf/pull/955) 修复 `hyperf/db` 组件的 `port` 和 `charset` 参数无效的问题；
- [#956](https://github.com/hyperf/hyperf/pull/956) 修复模型缓存中使用到`RedisHandler::incr` 在集群模式下会失败的问题；
- [#966](https://github.com/hyperf/hyperf/pull/966) 修复当在非 Worker 进程环境下使用分页器会报错的问题；
- [#968](https://github.com/hyperf/hyperf/pull/968) 修复当 `classes` 和 `annotations` 两种 Aspect 切入模式同时存在于一个类时，其中一个可能会失效的问题；
- [#980](https://github.com/hyperf/hyperf/pull/980) 修复 Session 组件内 `migrate`, `save` 核 `has` 方法无法使用的问题；
- [#982](https://github.com/hyperf/hyperf/pull/982) 修复 `Hyperf\GrpcClient\GrpcClient::yield` 在获取 Channel Pool 时没有通过正确的获取方式去获取的问题；
- [#987](https://github.com/hyperf/hyperf/pull/987) 修复通过 `gen:command` 命令生成的命令类缺少调用 `parent::configure()` 方法的问题；

## 优化

- [#991](https://github.com/hyperf/hyperf/pull/991) 优化 `Hyperf\DbConnection\ConnectionResolver::connection`的异常情况处理；

# v1.1.6 - 2019-11-14

## 新增

- [#827](https://github.com/hyperf/hyperf/pull/827) 新增了极简的高性能的 DB 组件；
- [#905](https://github.com/hyperf/hyperf/pull/905) 视图组件增加了 `twig` 模板引擎；
- [#911](https://github.com/hyperf/hyperf/pull/911) 定时任务支持多实例情况下，只运行单一实例的定时任务；
- [#913](https://github.com/hyperf/hyperf/pull/913) 增加监听器 `Hyperf\ExceptionHandler\Listener\ErrorExceptionHandler`；
- [#921](https://github.com/hyperf/hyperf/pull/921) 新增 `Session` 组件；
- [#931](https://github.com/hyperf/hyperf/pull/931) 阿波罗配置中心增加 `strict_mode`，自动将配置转化成对应数据类型；
- [#933](https://github.com/hyperf/hyperf/pull/933) 视图组件增加了 `plates` 模板引擎；
- [#937](https://github.com/hyperf/hyperf/pull/937) Nats 组件添加消费者消费和订阅事件；
- [#941](https://github.com/hyperf/hyperf/pull/941) 新增 `Zookeeper` 配置中心；

## 变更

- [#934](https://github.com/hyperf/hyperf/pull/934) 修改 `WaitGroup` 继承 `\Swoole\Coroutine\WaitGroup`；

## 修复

- [#897](https://github.com/hyperf/hyperf/pull/897) 修复 `Nats` 消费者，`pool` 配置无效的 BUG；
- [#901](https://github.com/hyperf/hyperf/pull/901) 修复 `GraphQL` 组件，`Factory` 注解无法正常使用的 BUG；
- [#903](https://github.com/hyperf/hyperf/pull/903) 修复添加 `hyperf/rpc-client` 依赖后，`init-proxy` 脚本无法正常停止的 BUG；
- [#904](https://github.com/hyperf/hyperf/pull/904) 修复监听器监听 `Hyperf\Framework\Event\BeforeMainServerStart` 事件时，无法使用 `IO` 操作的 BUG；
- [#906](https://github.com/hyperf/hyperf/pull/906) 修复 `Hyperf\HttpMessage\Server\Request` 端口获取有误的 BUG；
- [#907](https://github.com/hyperf/hyperf/pull/907) 修复 `Nats` 组件 `requestSync` 方法，超时时间不准确的 BUG；
- [#909](https://github.com/hyperf/hyperf/pull/909) 修复 `Parallel` 内逻辑抛错后，无法正常停止的 BUG；
- [#925](https://github.com/hyperf/hyperf/pull/925) 修复因 `Socket` 无法正常建立，导致进程频繁重启的 BUG；
- [#932](https://github.com/hyperf/hyperf/pull/932) 修复 `Translator::setLocale` 在协程环境下，数据混淆的 BUG；
- [#940](https://github.com/hyperf/hyperf/pull/940) 修复 `WebSocketClient::push` 方法 `finish` 参数类型错误；

## 优化

- [#907](https://github.com/hyperf/hyperf/pull/907) 优化 `Nats` 消费者频繁重启；
- [#928](https://github.com/hyperf/hyperf/pull/928) `Hyperf\ModelCache\Cacheable::query` 批量修改数据时，可以删除对应缓存；
- [#936](https://github.com/hyperf/hyperf/pull/936) 优化调用模型缓存 `increment` 时，可能因并发情况导致的数据有错；

# v1.1.5 - 2019-11-07

## 新增

- [#812](https://github.com/hyperf/hyperf/pull/812) 新增计划任务在集群下仅执行一次的支持；
- [#820](https://github.com/hyperf/hyperf/pull/820) 新增 hyperf/nats 组件；
- [#832](https://github.com/hyperf/hyperf/pull/832) 新增 `Hyperf\Utils\Codec\Json`；
- [#833](https://github.com/hyperf/hyperf/pull/833) 新增 `Hyperf\Utils\Backoff`；
- [#852](https://github.com/hyperf/hyperf/pull/852) 为 `Hyperf\Utils\Parallel` 新增 `clear()` 方法来清理所有已添加的回调；
- [#854](https://github.com/hyperf/hyperf/pull/854) 新增 `Hyperf\GraphQL\GraphQLMiddleware` 用于解析 GraphQL 请求；
- [#859](https://github.com/hyperf/hyperf/pull/859) 新增 Consul 集群的支持，现在可以从 Consul 集群中拉取服务提供者的节点信息；
- [#873](https://github.com/hyperf/hyperf/pull/873) 新增 Redis 集群的客户端支持；

## 修复

- [#831](https://github.com/hyperf/hyperf/pull/831) 修复 Redis 客户端连接在 Redis Server 重启后不会自动重连的问题；
- [#835](https://github.com/hyperf/hyperf/pull/835) 修复 `Request::inputs` 方法的默认值参数与预期效果不一致的问题；
- [#841](https://github.com/hyperf/hyperf/pull/841) 修复数据库迁移在多数据库的情况下连接无效的问题；
- [#844](https://github.com/hyperf/hyperf/pull/844) 修复 Composer 阅读器不支持根命名空间的用法的问题；
- [#846](https://github.com/hyperf/hyperf/pull/846) 修复 Redis 客户端的 `scan`, `hScan`, `zScan`, `sScan` 无法使用的问题；
- [#850](https://github.com/hyperf/hyperf/pull/850) 修复 Logger group 在 name 一样时不生效的问题；

## 优化

- [#832](https://github.com/hyperf/hyperf/pull/832) 优化了 Response 对象在转 JSON 格式时的异常处理逻辑；
- [#840](https://github.com/hyperf/hyperf/pull/840) 使用 `\Swoole\Timer::*` 来替代 `swoole_timer_*` 函数；
- [#859](https://github.com/hyperf/hyperf/pull/859) 优化了 RPC 客户端去 Consul 获取健康的节点信息的逻辑；

# v1.1.4 - 2019-10-31

## 新增

- [#778](https://github.com/hyperf/hyperf/pull/778) `Hyperf\Testing\Client` 新增 `PUT` 和 `DELETE`方法。
- [#784](https://github.com/hyperf/hyperf/pull/784) 新增服务监控组件。
- [#795](https://github.com/hyperf/hyperf/pull/795) `AbstractProcess` 增加 `restartInterval` 参数，允许子进程异常或正常退出后，延迟重启。
- [#804](https://github.com/hyperf/hyperf/pull/804) `Command` 增加事件 `BeforeHandle` `AfterHandle` 和 `FailToHandle`。

## 变更

- [#793](https://github.com/hyperf/hyperf/pull/793) `Pool::getConnectionsInChannel` 方法由 `protected` 改为 `public`.
- [#811](https://github.com/hyperf/hyperf/pull/811) 命令 `di:init-proxy` 不再主动清理代理缓存，如果想清理缓存请使用命令 `vendor/bin/init-proxy.sh`。

## 修复

- [#779](https://github.com/hyperf/hyperf/pull/779) 修复 `JPG` 文件验证不通过的问题。
- [#787](https://github.com/hyperf/hyperf/pull/787) 修复 `db:seed` 参数 `--class` 多余，导致报错的问题。
- [#795](https://github.com/hyperf/hyperf/pull/795) 修复自定义进程在异常抛出后，无法正常重启的 BUG。
- [#796](https://github.com/hyperf/hyperf/pull/796) 修复 `etcd` 配置中心 `enable` 即时设为 `false`，在项目启动时，依然会拉取配置的 BUG。

## 优化

- [#781](https://github.com/hyperf/hyperf/pull/781) 可以根据国际化组件配置发布验证器语言包到规定位置。
- [#796](https://github.com/hyperf/hyperf/pull/796) 优化 `ETCD` 客户端，不会多次创建 `HandlerStack`。 
- [#797](https://github.com/hyperf/hyperf/pull/797) 优化子进程重启

# v1.1.3 - 2019-10-24

## 新增

- [#745](https://github.com/hyperf/hyperf/pull/745) 为 `gen:model` 命令增加 `with-comments` 选项，以标记是否生成字段注释；
- [#747](https://github.com/hyperf/hyperf/pull/747) 为 AMQP 消费者增加 `AfterConsume`, `BeforeConsume`, `FailToConsume` 事件； 
- [#762](https://github.com/hyperf/hyperf/pull/762) 为 Parallel 特性增加协程控制功能；

## 变更

- [#767](https://github.com/hyperf/hyperf/pull/767) 重命名 `AbstractProcess` 的 `running` 属性名为 `listening`；

## 修复

- [#741](https://github.com/hyperf/hyperf/pull/741) 修复执行 `db:seed` 命令缺少文件名报错的问题；
- [#748](https://github.com/hyperf/hyperf/pull/748) 修复 `SymfonyNormalizer` 不处理 `array` 类型数据的问题；
- [#769](https://github.com/hyperf/hyperf/pull/769) 修复当 JSON RPC 响应的结果的 result 和 error 属性为 null 时会抛出一个无效请求的问题；

# v1.1.2 - 2019-10-17

## 新增

- [#722](https://github.com/hyperf-cloud/hyperf/pull/722) 为 AMQP Consumer 新增 `concurrent.limit` 配置来对协程消费进行速率限制；

## 变更

- [#678](https://github.com/hyperf-cloud/hyperf/pull/678) 为 `gen:model` 命令增加 `ignore-tables` 参数，同时默认屏蔽 `migrations` 表，即 `migrations` 表对应的模型在执行 `gen:model` 命令时不会生成；

## 修复

- [#694](https://github.com/hyperf-cloud/hyperf/pull/694) 修复 `Hyperf\Validation\Request\FormRequest` 的 `validationData` 方法不包含上传的文件的问题；
- [#700](https://github.com/hyperf-cloud/hyperf/pull/700) 修复 `Hyperf\HttpServer\Contract\ResponseInterface` 的 `download` 方法不能按预期运行的问题；
- [#701](https://github.com/hyperf-cloud/hyperf/pull/701) 修复自定义进程在出现未捕获的异常时不会自动重启的问题；
- [#704](https://github.com/hyperf-cloud/hyperf/pull/704) 修复 `Hyperf\Validation\Middleware\ValidationMiddleware` 在 action 参数没有定义参数类型时会报错的问题；
- [#713](https://github.com/hyperf-cloud/hyperf/pull/713) 修复当开启了注解缓存功能是，`ignoreAnnotations` 不能按预期工作的问题；
- [#717](https://github.com/hyperf-cloud/hyperf/pull/717) 修复 `getValidatorInstance` 方法会重复创建验证器对象的问题；
- [#724](https://github.com/hyperf-cloud/hyperf/pull/724) 修复 `db:seed` 命令在没有传 `database` 参数时会报错的问题； 
- [#729](https://github.com/hyperf-cloud/hyperf/pull/729) 修正组件配置项 `db:model` 为 `gen:model`；
- [#737](https://github.com/hyperf-cloud/hyperf/pull/737) 修复非 Worker 进程下无法使用 Tracer 组件来追踪调用链的问题；

# v1.1.1 - 2019-10-08

## Fixed

- [#664](https://github.com/hyperf/hyperf/pull/664) 调整通过 `gen:request` 命令生成 FormRequest 时 `authorize` 方法的默认返回值；
- [#665](https://github.com/hyperf/hyperf/pull/665) 修复启动时永远会自动生成代理类的问题；
- [#667](https://github.com/hyperf/hyperf/pull/667) 修复当访问一个不存在的路由时 `Hyperf\Validation\Middleware\ValidationMiddleware` 会抛出异常的问题；
- [#672](https://github.com/hyperf/hyperf/pull/672) 修复当 Action 方法上的参数类型为非对象类型时 `Hyperf\Validation\Middleware\ValidationMiddleware` 会抛出一个未捕获的异常的问题；
- [#674](https://github.com/hyperf/hyperf/pull/674) 修复使用 `gen:model` 命令从数据库生成模型时模型表名错误的问题；

# v1.1.0 - 2019-10-08

## 新增

- [#401](https://github.com/hyperf/hyperf/pull/401) 新增了 `Hyperf\HttpServer\Router\Dispatched` 对象来储存解析的路由信息，在用户中间件之前便解析完成以便后续的使用，同时也修复了路由里带参时中间件失效的问题；
- [#402](https://github.com/hyperf/hyperf/pull/402) 新增 `@AsyncQueueMessage` 注解，通过定义此注解在方法上，表明这个方法的实际运行逻辑是投递给 Async-Queue 队列去消费；
- [#418](https://github.com/hyperf/hyperf/pull/418) 允许发送 WebSocket 消息到任意的 fd，即使当前的 Worker 进程不持有对应的 fd，框架会自动进行进程间通讯来实现发送；
- [#420](https://github.com/hyperf/hyperf/pull/420) 为数据库模型增加新的事件机制，与 PSR-15 的事件调度器相配合，可以解耦的定义 Listener 来监听模型事件；
- [#429](https://github.com/hyperf/hyperf/pull/429) [#643](https://github.com/hyperf/hyperf/pull/643) 新增 Validation 表单验证器组件，这是一个衍生于 [illuminate/validation](https://github.com/illuminate/validation) 的组件，感谢 Laravel 开发组提供如此好用的验证器组件，；
- [#441](https://github.com/hyperf/hyperf/pull/441) 当 Redis 连接处于低使用频率的情况下自动关闭空闲连接；
- [#478](https://github.com/hyperf/hyperf/pull/441) 更好的适配 OpenTracing 协议，同时适配 [Jaeger](https://www.jaegertracing.io/)，Jaeger 是一款优秀的开源的端对端分布式调用链追踪系统；
- [#500](https://github.com/hyperf/hyperf/pull/499) 为 `Hyperf\HttpServer\Contract\ResponseInterface` 增加链式方法调用支持，解决调用了代理方法的方法后无法再调用原始方法的问题；
- [#523](https://github.com/hyperf/hyperf/pull/523) 为  `gen:model` 命令新增了 `table-mapping` 选项；
- [#555](https://github.com/hyperf/hyperf/pull/555) 新增了一个全局函数 `swoole_hook_flags` 来获取由常量 `SWOOLE_HOOK_FLAGS` 所定义的 Runtime Hook 等级，您可以在 `bin/hyperf.php` 通过 `! defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', SWOOLE_HOOK_ALL);` 的方式来定义该常量，即 Runtime Hook 等级；
- [#596](https://github.com/hyperf/hyperf/pull/596)  为`@Inject` 注解增加了  `required` 参数，当您定义 `@Inject(required=false)` 注解到一个成员属性上，那么当该依赖项不存在时也不会抛出 `Hyperf\Di\Exception\NotFoundException` 异常，而是以默认值 `null` 来注入， `required` 参数的默认值为 `true`，当在构造器注入的情况下，您可以通过对构造器的参数定义为 `nullable` 来达到同样的目的；
- [#597](https://github.com/hyperf/hyperf/pull/597) 为 AsyncQueue 组件的消费者增加 `Concurrent` 来控制消费速率；
- [#599](https://github.com/hyperf/hyperf/pull/599) 为 AsyncQueue 组件的消费者增加根据当前重试次数来设定该消息的重试等待时长的功能，可以为消息设置阶梯式的重试等待；
- [#619](https://github.com/hyperf/hyperf/pull/619) 为 Guzzle 客户端增加 HandlerStackFactory 类，以便更便捷地创建一个 HandlerStack；
- [#620](https://github.com/hyperf/hyperf/pull/620) 为 AsyncQueue 组件的消费者增加自动重启的机制；
- [#629](https://github.com/hyperf/hyperf/pull/629) 允许通过配置文件的形式为 Apollo 客户端定义  `clientIp`, `pullTimeout`, `intervalTimeout` 配置；
- [#647](https://github.com/hyperf/hyperf/pull/647) 根据 server 的配置，自动为 TCP Response 追加 `eof`；
- [#648](https://github.com/hyperf/hyperf/pull/648) 为 AMQP Consumer 增加 `nack` 的返回类型，当消费逻辑返回 `Hyperf\Amqp\Result::NACK` 时抽象消费者会以 `basic_nack` 方法来响应消息；
- [#654](https://github.com/hyperf/hyperf/pull/654) 增加所有 Swoole Event 的默认回调和对应的 Hyperf 事件；

## 变更

- [#437](https://github.com/hyperf/hyperf/pull/437) `Hyperf\Testing\Client` 在遇到异常时不再直接抛出异常而是交给 ExceptionHandler 流程处理；
- [#463](https://github.com/hyperf/hyperf/pull/463) 简化了 `container.php` 文件及优化了注解缓存机制；

新的 config/container.php 文件内容如下：

```php
<?php

use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSourceFactory;
use Hyperf\Utils\ApplicationContext;

$container = new Container((new DefinitionSourceFactory(true))());

if (! $container instanceof \Psr\Container\ContainerInterface) {
    throw new RuntimeException('The dependency injection container is invalid.');
}
return ApplicationContext::setContainer($container);
```

- [#486](https://github.com/hyperf/hyperf/pull/486) `Hyperf\HttpMessage\Server\Request` 的 `getParsedBody` 方法现在可以直接处理 JSON 格式的数据了；
- [#523](https://github.com/hyperf/hyperf/pull/523) 调整 `gen:model` 命令生成的模型类名默认为单数，如果表名为复数，则默认生成的类名为单数；
- [#614](https://github.com/hyperf/hyperf/pull/614) [#617](https://github.com/hyperf/hyperf/pull/617) 调整了 ConfigProvider 类的结构, 同时将 `config/dependencies.php` 文件移动到了 `config/autoload/dependencies.php` 内，且文件结构去除了 `dependencies` 层，此后也意味着您也可以将 `dependencies` 配置写到 `config/config.php` 文件内；

Config Provider 内数据结构的变化：
之前：

```php
'scan' => [
    'paths' => [
        __DIR__,
    ],
    'collectors' => [],
],
```

现在：

```php
'annotations' => [
    'scan' => [
        'paths' => [
            __DIR__,
        ],
        'collectors' => [],
    ],
],
```

> 增加了一层 annotations，这样将与配置文件结构一致，不再特殊

- [#630](https://github.com/hyperf/hyperf/pull/630) 变更了 `Hyperf\HttpServer\CoreMiddleware` 类的实例化方式，使用 `make()` 来替代了 `new`；
- [#631](https://github.com/hyperf/hyperf/pull/631) 变更了 AMQP Consumer 的实例化方式，使用 `make()` 来替代了 `new`；
- [#637](https://github.com/hyperf/hyperf/pull/637) 调整了 `Hyperf\Contract\OnMessageInterface` 和 `Hyperf\Contract\OnOpenInterface` 的第一个参数的类型约束， 使用 `Swoole\WebSocket\Server` 替代 `Swoole\Server`；
- [#638](https://github.com/hyperf/hyperf/pull/638) 重命名了 `db:model` 命令为 `gen:model` 命令，同时增加了一个 Visitor 来优化创建的 `$connection` 成员属性，如果要创建的模型类的 `$connection` 属性的值与继承的父类一致，那么创建的模型类将不会包含此属性；

## 移除

- [#401](https://github.com/hyperf/hyperf/pull/401) 移除了 `Hyperf\JsonRpc\HttpServerFactory`, `Hyperf\HttpServer\ServerFactory`, `Hyperf\GrpcServer\ServerFactory` 类；
- [#402](https://github.com/hyperf/hyperf/pull/402) 移除了弃用的 `AsyncQueue::delay` 方法；
- [#563](https://github.com/hyperf/hyperf/pull/563) 移除了弃用的 `Hyperf\Server\ServerInterface::SERVER_TCP` 常量，使用 `Hyperf\Server\ServerInterface::SERVER_BASE` 来替代；
- [#602](https://github.com/hyperf/hyperf/pull/602) 移除了 `Hyperf\Utils\Coroutine\Concurrent` 的 `timeout` 参数；
- [#612](https://github.com/hyperf/hyperf/pull/612) 移除了 RingPHP Handler 里没有使用到的 `$url` 变量；
- [#616](https://github.com/hyperf/hyperf/pull/616) [#618](https://github.com/hyperf/hyperf/pull/618) 移除了 Guzzle 里一些无用的代码；

## 优化

- [#644](https://github.com/hyperf/hyperf/pull/644) 优化了注解扫描的流程，分开 `app` 和 `vendor` 两部分来扫描注解，大大减少了用户的扫描耗时；
- [#653](https://github.com/hyperf/hyperf/pull/653) 优化了 Swoole shortname 的检测逻辑，现在的检测逻辑更加贴合 Swoole 的实际配置场景，也不只是 `swoole.use_shortname = "Off"` 才能通过检测了；

## 修复

- [#448](https://github.com/hyperf/hyperf/pull/448) 修复了当 HTTP Server 或 WebSocket Server 存在时，TCP Server 有可能无法启动的问题；
- [#623](https://github.com/hyperf/hyperf/pull/623) 修复了当传递一个 `null` 值到代理类的方法参数时，方法仍然会获取方法默认值的问题；

# v1.0.16 - 2019-09-20

## 新增

- [#565](https://github.com/hyperf/hyperf/pull/565) 增加对 Redis 客户端的 `options` 配置参数支持；
- [#580](https://github.com/hyperf/hyperf/pull/580) 增加协程并发控制特性，通过 `Hyperf\Utils\Coroutine\Concurrent` 可以实现一个代码块内限制同时最多运行的协程数量；

## 变更

- [#583](https://github.com/hyperf/hyperf/pull/583) 当 `BaseClient::start` 失败时会抛出 `Hyperf\GrpcClient\Exception\GrpcClientException` 异常；
- [#585](https://github.com/hyperf/hyperf/pull/585) 当投递到 TaskWorker 执行的 Task 失败时，会回传异常到 Worker 进程中；

## 修复

- [#564](https://github.com/hyperf/hyperf/pull/564) 修复某些情况下 `Coroutine\Http2\Client->send` 返回值不正确的问题；
- [#567](https://github.com/hyperf/hyperf/pull/567) 修复当 JSON RPC 消费者配置 name 不是接口时，无法生成代理类的问题；
- [#571](https://github.com/hyperf/hyperf/pull/571) 修复 ExceptionHandler 的 `stopPropagation` 的协程变量污染的问题；
- [#579](https://github.com/hyperf/hyperf/pull/579) 动态初始化 `snowflake`  的 MetaData，主要修复当在命令模式下使用 Snowflake 时，比如 `di:init-proxy` 命令，会连接到 Redis 服务器至超时；

# v1.0.15 - 2019-09-11

## 修复

- [#534](https://github.com/hyperf/hyperf/pull/534) 修复 Guzzle HTTP 客户端的 `CoroutineHanlder` 没有处理状态码为 `-3` 的情况；
- [#541](https://github.com/hyperf/hyperf/pull/541) 修复 gRPC 客户端的 `$client` 参数设置错误的问题；
- [#542](https://github.com/hyperf/hyperf/pull/542) 修复 `Hyperf\Grpc\Parser::parseResponse` 无法支持 gRPC 标准状态码的问题；
- [#551](https://github.com/hyperf/hyperf/pull/551) 修复当服务端关闭了 gRPC 连接时，gRPC 客户端会残留一个死循环的协程；
- [#558](https://github.com/hyperf/hyperf/pull/558) 修复 `UDP Server` 无法正确配置启动的问题；

## 优化

- [#549](https://github.com/hyperf/hyperf/pull/549) 优化了 `Hyperf\Amqp\Connection\SwooleIO` 的 `read` 和 `write` 方法，减少不必要的重试；
- [#559](https://github.com/hyperf/hyperf/pull/559) 优化 `Hyperf\HttpServer\Response::redirect()` 方法，自动识别链接首位是否为斜杠并合理修正参数；
- [#560](https://github.com/hyperf/hyperf/pull/560) 优化 `Hyperf\WebSocketServer\CoreMiddleware`，移除了不必要的代码；

## 移除

- [#545](https://github.com/hyperf/hyperf/pull/545) 移除了 `Hyperf\Database\Model\SoftDeletes` 内无用的 `restoring` 和 `restored` 静态方法； 

## 即将移除

- [#558](https://github.com/hyperf/hyperf/pull/558) 标记了 `Hyperf\Server\ServerInterface::SERVER_TCP` 常量为 `弃用` 状态，该常量将于 `v1.1` 移除，由更合理的 `Hyperf\Server\ServerInterface::SERVER_BASE` 常量替代；

# v1.0.14 - 2019-09-05

## 新增

- [#389](https://github.com/hyperf/hyperf/pull/389) [#419](https://github.com/hyperf/hyperf/pull/419) [#432](https://github.com/hyperf/hyperf/pull/432) [#524](https://github.com/hyperf/hyperf/pull/524) 新增 Snowflake 官方组件, Snowflake 是一个由 Twitter 提出的分布式全局唯一 ID 生成算法，[hyperf/snowflake](https://github.com/hyperf/snowflake) 组件实现了该算法并设计得易于使用，同时在设计上提供了很好的可扩展性，可以很轻易的将该组件转换成其它基于 Snowflake 算法的变体算法；
- [#525](https://github.com/hyperf/hyperf/pull/525) 为 `Hyperf\HttpServer\Contract\ResponseInterface` 增加一个 `download()` 方法，提供便捷的下载响应返回；

## 变更

- [#482](https://github.com/hyperf/hyperf/pull/482) 生成模型文件时，当设置了 `refresh-fillable` 选项时重新生成模型的 `fillable` 属性，同时该命令的默认情况下将不会再覆盖生成 `fillable` 属性；
- [#501](https://github.com/hyperf/hyperf/pull/501) 当 `Mapping` 注解的 `path` 属性为一个空字符串时，那么该路由则为 `/prefix`；
- [#513](https://github.com/hyperf/hyperf/pull/513) 如果项目设置了 `app_name` 属性，则进程名称会自动带上该名称；
- [#508](https://github.com/hyperf/hyperf/pull/508) [#526](https://github.com/hyperf/hyperf/pull/526) 当在非协程环境下执行 `Hyperf\Utils\Coroutine::parentId()` 方法时会返回一个 `null` 值；

## 修复

- [#479](https://github.com/hyperf/hyperf/pull/479) 修复了当 Elasticsearch client 的 `host` 属性设置有误时，返回类型错误的问题；
- [#514](https://github.com/hyperf/hyperf/pull/514) 修复当 Redis 密码配置为空字符串时鉴权失败的问题；
- [#527](https://github.com/hyperf/hyperf/pull/527) 修复 Translator 无法重复翻译的问题；

# v1.0.13 - 2019-08-28

## 新增

- [#449](https://github.com/hyperf/hyperf/pull/428) 新增一个独立组件 [hyperf/translation](https://github.com/hyperf/translation)， 衍生于 [illuminate/translation](https://github.com/illuminate/translation)；
- [#449](https://github.com/hyperf/hyperf/pull/449) 为 GRPC-Server 增加标准错误码；
- [#450](https://github.com/hyperf/hyperf/pull/450) 为 `Hyperf\Database\Schema\Schema` 类的魔术方法增加对应的静态方法注释，为 IDE 提供代码提醒的支持；

## 变更

- [#451](https://github.com/hyperf/hyperf/pull/451) 在使用 `@AutoController` 注解时不再会自动为魔术方法生成对应的路由；
- [#468](https://github.com/hyperf/hyperf/pull/468) 让 GRPC-Server 和 HTTP-Server 提供的异常处理器处理所有的异常，而不只是 `ServerException`；

## 修复 

- [#466](https://github.com/hyperf/hyperf/pull/466) 修复分页时数据不足时返回类型错误的问题；
- [#466](https://github.com/hyperf/hyperf/pull/470) 优化了 `vendor:publish` 命令，当要生成的目标文件夹存在时，不再重复生成；

# v1.0.12 - 2019-08-21

## 新增

- [#405](https://github.com/hyperf/hyperf/pull/405) 增加 `Hyperf\Utils\Context::override()` 方法，现在你可以通过 `override` 方法获取某些协程上下文的值并修改覆盖它；
- [#415](https://github.com/hyperf/hyperf/pull/415) 对 Logger 的配置文件增加多个 Handler 的配置支持；

## 变更

- [#431](https://github.com/hyperf/hyperf/pull/431) 移除了 `Hyperf\GrpcClient\GrpcClient::openStream()` 的第 3 个参数，这个参数不会影响实际使用；

## 修复

- [#414](https://github.com/hyperf/hyperf/pull/414) 修复 `Hyperf\WebSockerServer\Exception\Handler\WebSocketExceptionHandler` 内的变量名称错误的问题；
- [#424](https://github.com/hyperf/hyperf/pull/424) 修复 Guzzle 在使用 `Hyperf\Guzzle\CoroutineHandler` 时配置 `proxy` 参数时不支持数组传值的问题；
- [#430](https://github.com/hyperf/hyperf/pull/430) 修复 `Hyperf\HttpServer\Request::file()` 当以一个 Name 上传多个文件时，返回格式不正确的问题；
- [#431](https://github.com/hyperf/hyperf/pull/431) 修复 GRPC Client 的 Request 对象在发送 Force-Close 请求时缺少参数的问题；

# v1.0.11 - 2019-08-15

## 新增

- [#366](https://github.com/hyperf/hyperf/pull/366) 增加 `Hyperf\Server\Listener\InitProcessTitleListener` 监听者来设置进程名称， 同时增加了 `Hyperf\Framework\Event\OnStart` 和 `Hyperf\Framework\Event\OnManagerStart` 事件；

## 修复

- [#361](https://github.com/hyperf/hyperf/pull/361) 修复 `db:model`命令在 MySQL 8 下不能正常运行；
- [#369](https://github.com/hyperf/hyperf/pull/369) 修复实现 `\Serializable` 接口的自定义异常类不能正确的序列化和反序列化问题；
- [#384](https://github.com/hyperf/hyperf/pull/384) 修复用户自定义的 `ExceptionHandler` 在 JSON RPC Server 下无法正常工作的问题，因为框架默认自动处理了对应的异常；
- [#370](https://github.com/hyperf/hyperf/pull/370) 修复了 `Hyperf\GrpcClient\BaseClient` 的 `$client` 属性在流式传输的时候设置了错误的类型的值的问题, 同时增加了默认的 `content-type`  为 `application/grpc+proto`，以及允许用户通过自定义 `Request` 对象来重写 `buildRequest()` 方法；

## 变更

- [#356](https://github.com/hyperf/hyperf/pull/356) [#390](https://github.com/hyperf/hyperf/pull/390) 优化 aysnc-queue 组件当生成 Job 时，如果 Job 实现了 `Hyperf\Contract\CompressInterface`，那么 Job 对象会被压缩为一个更小的对象；
- [#358](https://github.com/hyperf/hyperf/pull/358) 只有当 `$enableCache` 为 `true` 时才生成注解缓存文件；
- [#359](https://github.com/hyperf/hyperf/pull/359) [#390](https://github.com/hyperf/hyperf/pull/390) 为 `Collection` 和 `Model` 增加压缩能力，当类实现 `Hyperf\Contract\CompressInterface` 可通过 `compress` 方法生成一个更小的对象；

# v1.0.10 - 2019-08-09

## 新增

- [#321](https://github.com/hyperf/hyperf/pull/321) 为 HTTP Server 的 Controller/RequestHandler 参数增加自定义对象类型的数组支持，特别适用于 JSON RPC 下，现在你可以通过在方法上定义 `@var Object[]` 来获得框架自动反序列化对应对象的支持；
- [#324](https://github.com/hyperf/hyperf/pull/324) 增加一个实现于 `Hyperf\Contract\IdGeneratorInterface` 的 ID 生成器 `NodeRequestIdGenerator`；
- [#336](https://github.com/hyperf/hyperf/pull/336) 增加动态代理的 RPC 客户端功能；
- [#346](https://github.com/hyperf/hyperf/pull/346) [#348](https://github.com/hyperf/hyperf/pull/348) 为 `hyperf/cache` 缓存组件增加文件驱动；

## 变更

- [#330](https://github.com/hyperf/hyperf/pull/330) 当扫描的 $paths 为空时，不输出扫描信息；
- [#328](https://github.com/hyperf/hyperf/pull/328) 根据 Composer 的 PSR-4 定义的规则加载业务项目；
- [#329](https://github.com/hyperf/hyperf/pull/329) 优化 JSON RPC 服务端和客户端的异常消息处理；
- [#340](https://github.com/hyperf/hyperf/pull/340) 为 `make` 函数增加索引数组的传参方式；
- [#349](https://github.com/hyperf/hyperf/pull/349) 重命名下列类，修正由于拼写错误导致的命名错误；

|                     原类名                      |                  修改后的类名                     |
|:----------------------------------------------|:-----------------------------------------------|
| Hyperf\\Database\\Commands\\Ast\\ModelUpdateVistor | Hyperf\\Database\\Commands\\Ast\\ModelUpdateVisitor |
|       Hyperf\\Di\\Aop\\ProxyClassNameVistor       |       Hyperf\\Di\\Aop\\ProxyClassNameVisitor       |
|         Hyperf\\Di\\Aop\\ProxyCallVistor          |         Hyperf\\Di\\Aop\\ProxyCallVisitor          |

## 修复

- [#325](https://github.com/hyperf/hyperf/pull/325) 优化 RPC 服务注册时会多次调用 Consul Services 的问题；
- [#332](https://github.com/hyperf/hyperf/pull/332) 修复 `Hyperf\Tracer\Middleware\TraceMiddeware` 在新版的 openzipkin/zipkin 下的类型约束错误；
- [#333](https://github.com/hyperf/hyperf/pull/333) 修复 `Redis::delete()` 方法在 5.0 版不存在的问题；
- [#334](https://github.com/hyperf/hyperf/pull/334) 修复向阿里云 ACM 配置中心拉取配置时，部分情况下部分配置无法更新的问题；
- [#337](https://github.com/hyperf/hyperf/pull/337) 修复当 Header 的 key 为非字符串类型时，会返回 500 响应的问题；
- [#338](https://github.com/hyperf/hyperf/pull/338) 修复 `ProviderConfig::load` 在遇到重复 key 时会导致在深度合并时将字符串转换成数组的问题；

# v1.0.9 - 2019-08-03

## 新增

- [#317](https://github.com/hyperf/hyperf/pull/317) 增加 `composer-json-fixer` 来优化 composer.json 文件的内容；
- [#320](https://github.com/hyperf/hyperf/pull/320) DI 定义 Definition 时，允许 value 为一个匿名函数；

## 修复

- [#300](https://github.com/hyperf/hyperf/pull/300) 让 AsyncQueue 的消息于子协程内来进行处理，修复 `attempts` 参数与实际重试次数不一致的问题；
- [#305](https://github.com/hyperf/hyperf/pull/305) 修复 `Hyperf\Utils\Arr::set` 方法的 `$key` 参数不支持 `int` 个 `null` 的问题；
- [#312](https://github.com/hyperf/hyperf/pull/312) 修复 `Hyperf\Amqp\BeforeMainServerStartListener` 监听器的优先级错误的问题；
- [#315](https://github.com/hyperf/hyperf/pull/315) 修复 ETCD 配置中心在 Worker 进程重启后或在自定义进程内无法使用问题；
- [#318](https://github.com/hyperf/hyperf/pull/318) 修复服务会持续注册到服务中心的问题；

## 变更

- [#323](https://github.com/hyperf/hyperf/pull/323) 强制转换 `Cacheable` 和 `CachePut` 注解的 `$ttl` 属性为 `int` 类型；

# v1.0.8 - 2019-07-31

## 新增

- [#276](https://github.com/hyperf/hyperf/pull/276) AMQP 消费者支持配置及绑定多个 `routing_key`；
- [#277](https://github.com/hyperf/hyperf/pull/277) 增加 ETCD 客户端组件及 ETCD 配置中心组件；

## 变更

- [#297](https://github.com/hyperf/hyperf/pull/297) 如果服务注册失败，会于 10 秒后重试注册，且屏蔽了连接不上服务中心(Consul)而抛出的异常；
- [#298](https://github.com/hyperf/hyperf/pull/298) [#301](https://github.com/hyperf/hyperf/pull/301) 适配 `openzipkin/zipkin` v1.3.3+ 版本；

## 修复

- [#271](https://github.com/hyperf/hyperf/pull/271) 修复了 AOP 在 `classes` 只会策略下配置同一个类的多个方法只会实现第一个方法的代理方法的问题；
- [#285](https://github.com/hyperf/hyperf/pull/285) 修复了 AOP 在匿名类下生成节点存在丢失的问题；
- [#286](https://github.com/hyperf/hyperf/pull/286) 自动 `rollback` 没有 `commit` 或 `rollback` 的 MySQL 连接；
- [#292](https://github.com/hyperf/hyperf/pull/292) 修复了 `Request::header` 方法的 `$default` 参数无效的问题；
- [#293](https://github.com/hyperf/hyperf/pull/293) 修复了 `Arr::get` 方法的 `$key` 参数不支持 `int` and `null` 传值的问题；

# v1.0.7 - 2019-07-26

## 修复

- [#266](https://github.com/hyperf/hyperf/pull/266) 修复投递 AMQP 消息时的超时逻辑；
- [#273](https://github.com/hyperf/hyperf/pull/273) 修复当有一个服务注册到服务中心的时候所有服务会被移除的问题；
- [#274](https://github.com/hyperf/hyperf/pull/274) 修复视图响应的 Content-Type ；

# v1.0.6 - 2019-07-24

## 新增

- [#203](https://github.com/hyperf/hyperf/pull/203) [#236](https://github.com/hyperf/hyperf/pull/236) [#247](https://github.com/hyperf/hyperf/pull/247) [#252](https://github.com/hyperf/hyperf/pull/252) 增加视图组件，支持 Blade 引擎和 Smarty 引擎； 
- [#203](https://github.com/hyperf/hyperf/pull/203) 增加 Task 组件，适配 Swoole Task 机制；
- [#245](https://github.com/hyperf/hyperf/pull/245) 增加 TaskWorkerStrategy 和 WorkerStrategy 两种定时任务调度策略.
- [#251](https://github.com/hyperf/hyperf/pull/251) 增加用协程上下文作为储存的缓存驱动；
- [#254](https://github.com/hyperf/hyperf/pull/254) 增加 `RequestMapping::$methods` 对数组传值的支持, 现在可以通过 `@RequestMapping(methods={"GET"})` 和 `@RequestMapping(methods={RequestMapping::GET})` 两种新的方式定义方法；
- [#255](https://github.com/hyperf/hyperf/pull/255) 控制器返回 `Hyperf\Utils\Contracts\Arrayable` 会自动转换为 Response 对象, 同时对返回字符串的响应对象增加  `text/plain` Content-Type;
- [#256](https://github.com/hyperf/hyperf/pull/256) 如果 `Hyperf\Contract\IdGeneratorInterface` 存在容器绑定关系, 那么 `json-rpc` 客户端会根据该类自动生成一个请求 ID 并储存在 Request attribute 里，同时完善了 `JSON RPC` 在 TCP 协议下的服务注册及健康检查；

## 变更

- [#247](https://github.com/hyperf/hyperf/pull/247) 使用 `WorkerStrategy` 作为默认的计划任务调度策略；
- [#256](https://github.com/hyperf/hyperf/pull/256) 优化 `JSON RPC` 的错误处理，现在当方法不存在时也会返回一个标准的 `JSON RPC` 错误对象；

## 修复

- [#235](https://github.com/hyperf/hyperf/pull/235) 为 `grpc-server` 增加了默认的错误处理器，防止错误抛出.
- [#240](https://github.com/hyperf/hyperf/pull/240) 优化了 OnPipeMessage 事件的触发，修复会被多个监听器获取错误数据的问题；
- [#257](https://github.com/hyperf/hyperf/pull/257) 修复了在某些环境下无法获得内网 IP 的问题；

# v1.0.5 - 2019-07-17

## 新增

- [#185](https://github.com/hyperf/hyperf/pull/185) `响应(Response)` 增加 `xml` 格式支持；
- [#202](https://github.com/hyperf/hyperf/pull/202) 在协程内抛出未捕获的异常时，默认输出异常的 trace 信息；
- [#138](https://github.com/hyperf/hyperf/pull/138) [#197](https://github.com/hyperf/hyperf/pull/197) 增加秒级定时任务组件；

# 变更

- [#195](https://github.com/hyperf/hyperf/pull/195) 变更 `retry()` 函数的 `$times` 参数的行为意义, 表示重试的次数而不是执行的次数；
- [#198](https://github.com/hyperf/hyperf/pull/198) 优化 `Hyperf\Di\Container` 的 `has()` 方法, 当传递一个不可实例化的示例（如接口）至 `$container->has($interface)` 方法时，会返回 `false`；
- [#199](https://github.com/hyperf/hyperf/pull/199) 当生产 AMQP 消息失败时，会自动重试一次；
- [#200](https://github.com/hyperf/hyperf/pull/200) 通过 Git 打包项目的部署包时，不再包含 `tests` 文件夹；

## 修复

- [#176](https://github.com/hyperf/hyperf/pull/176) 修复 `LengthAwarePaginator::nextPageUrl()` 方法返回值的类型约束；
- [#188](https://github.com/hyperf/hyperf/pull/188) 修复 Guzzle Client 的代理设置不生效的问题；
- [#211](https://github.com/hyperf/hyperf/pull/211) 修复 RPC Client 存在多个时会被最后一个覆盖的问题；
- [#212](https://github.com/hyperf/hyperf/pull/212) 修复 Guzzle Client 的 `ssl_key` 和 `cert` 配置项不能正常工作的问题；

# v1.0.4 - 2019-07-08

## 新增

- [#140](https://github.com/hyperf/hyperf/pull/140) 支持 Swoole v4.4.0.
- [#152](https://github.com/hyperf/hyperf/pull/152) 数据库连接在低使用率时连接池会自动释放连接
- [#163](https://github.com/hyperf/hyperf/pull/163) constants 组件的`AbstractConstants::__callStatic` 支持自定义参数

## 变更

- [#124](https://github.com/hyperf/hyperf/pull/124) `DriverInterface::push` 增加 `$delay` 参数用于设置延迟时间, 同时 `DriverInterface::delay` 将标记为弃用的，将于 1.1 版本移除 
- [#125](https://github.com/hyperf/hyperf/pull/125) 更改 `config()` 函数的 `$default` 参数的默认值为 `null`.

## 修复

- [#110](https://github.com/hyperf/hyperf/pull/110) [#111](https://github.com/hyperf/hyperf/pull/111) 修复 `Redis::select` 无法正常切换数据库的问题
- [#131](https://github.com/hyperf/hyperf/pull/131) 修复 `middlewares` 配置在 `Router::addGroup` 下无法正常设置的问题
- [#132](https://github.com/hyperf/hyperf/pull/132) 修复 `request->hasFile` 判断条件错误的问题
- [#135](https://github.com/hyperf/hyperf/pull/135) 修复 `response->redirect` 在调整外链时无法正确生成链接的问题
- [#139](https://github.com/hyperf/hyperf/pull/139) 修复 ConsulAgent 的 URI 无法自定义设置的问题
- [#148](https://github.com/hyperf/hyperf/pull/148) 修复当 `migrates` 文件夹不存在时无法生成迁移模板的问题
- [#169](https://github.com/hyperf/hyperf/pull/169) 修复处理请求时没法正确处理数组类型的参数
- [#170](https://github.com/hyperf/hyperf/pull/170) 修复当路由不存在时 WebSocket Server 无法正确捕获异常的问题

## 移除

- [#131](https://github.com/hyperf/hyperf/pull/131) 移除 `Router` `options` 里的 `server` 参数

# v1.0.3 - 2019-07-02

## 新增

- [#48](https://github.com/hyperf/hyperf/pull/48) 增加 WebSocket 协程客户端及服务端
- [#51](https://github.com/hyperf/hyperf/pull/51) 增加了 `enableCache` 参数去控制 `DefinitionSource` 是否启用注解扫描缓存 
- [#61](https://github.com/hyperf/hyperf/pull/61) 通过 `db:model` 命令创建模型时增加属性类型
- [#65](https://github.com/hyperf/hyperf/pull/65) 模型缓存增加 JSON 格式支持

## 变更

- [#46](https://github.com/hyperf/hyperf/pull/46) 移除了 `hyperf/di`, `hyperf/command` and `hyperf/dispatcher` 组件对 `hyperf/framework` 组件的依赖

## 修复

- [#45](https://github.com/hyperf/hyperf/pull/55) 修复当引用了 `hyperf/websocket-server` 组件时有可能会导致 HTTP Server 启动失败的问题
- [#55](https://github.com/hyperf/hyperf/pull/55) 修复方法级别的 `@Middleware` 注解可能会被覆盖的问题
- [#73](https://github.com/hyperf/hyperf/pull/73) 修复 `db:model` 命令对短属性处理不正确的问题
- [#88](https://github.com/hyperf/hyperf/pull/88) 修复当控制器存在多层文件夹时生成的路由可能不正确的问题
- [#101](https://github.com/hyperf/hyperf/pull/101) 修复常量不存在 `@Message` 注解时会报错的问题

# v1.0.2 - 2019-06-25

## 新增

- 接入 Travis CI，目前 Hyperf 共存在 426 个单测，1124 个断言； [#25](https://github.com/hyperf/hyperf/pull/25)
- 完善了对 `Redis::connect` 方法的参数支持； [#29](https://github.com/hyperf/hyperf/pull/29)

## 修复

- 修复了 HTTP Server 会被 WebSocket Server 影响的问题（WebSocket Server 尚未发布）；
- 修复了代理类部分注解没有生成的问题；
- 修复了数据库连接池在单测环境下会无法获取连接的问题；
- 修复了 co-phpunit 在某些情况下不能按预期运行的问题；
- 修复了模型事件 `creating`, `updating` ... 运行与预期不一致的问题；
- 修复了 `flushContext` 方法在单测环境下不能按预期运行的问题；
