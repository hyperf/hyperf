# Jet

Jet 是一个统一模型的 RPC 客户端，内置 JSONRPC 协议的适配，该组件可适用于所有的 PHP 环境，包括 PHP-FPM 和 Swoole 或 Hyperf。（在 Hyperf 环境下，目前仍建议直接使用 `hyperf/json-rpc` 组件来作为客户端使用）

> 未来还会内置 gRPC 和 Tars 协议。

# 安装

```bash
composer require hyperf/jet
```

# 快速开始

## 注册协议

> 注册协议不是必须的一个步骤，但您可以通过 ProtocolManager 管理所有的协议。

您可以通过 `Hyperf\Jet\ProtocolManager` 类来注册管理任意的协议，每个协议会包含 Transporter, Packer, DataFormatter and PathGenerator 几个基本的组件，您可以注册一个 JSONRPC 协议，如下：

```php
<?php

use Hyperf\Jet\DataFormatter\DataFormatter;
use Hyperf\Jet\Packer\JsonEofPacker;
use Hyperf\Jet\PathGenerator\PathGenerator;
use Hyperf\Jet\ProtocolManager;
use Hyperf\Jet\Transporter\StreamSocketTransporter;

ProtocolManager::register($protocol = 'jsonrpc', [
    ProtocolManager::TRANSPORTER => new StreamSocketTransporter(),
    ProtocolManager::PACKER => new JsonEofPacker(),
    ProtocolManager::PATH_GENERATOR => new PathGenerator(),
    ProtocolManager::DATA_FORMATTER => new DataFormatter(),
]);
```

## 注册服务

> 注册服务不是必须的一个步骤，但您可以通过 ServiceManager 管理所有的服务。

在您往 `Hyperf\Jet\ProtocolManager` 注册了一个协议之后，您可以通过 `Hyperf\Jet\ServiceManager` 将协议绑定到任意的服务上，如下：

```php
<?php
use Hyperf\Jet\ServiceManager;

// 绑定 CalculatorService 与 jsonrpc 协议，同时设定静态的节点信息
ServiceManager::register($service = 'CalculatorService', $protocol = 'jsonrpc', [
    ServiceManager::NODES => [
        [$host = '127.0.0.1', $port = 9503],
    ],
]);
```

## 调用 RPC 方法

### 通过 ClientFactory 调用

在您注册完协议与服务之后，您可以通过 `Hyperf/Jet/ClientFactory` 来获得您的服务的客户端，如下所示：

```php
<?php
use Hyperf\Jet\ClientFactory;

$clientFactory = new ClientFactory();
$client = $clientFactory->create($service = 'CalculatorService', $protocol = 'jsonrpc');
```

当您拥有 client 对象后，您可以通过该对象调用任意的远程方法，如下：

```php
// 调用远程方法 `add` 并带上参数 `1` 和 `2`
// $result 即为远程方法的返回值
$result = $client->add(1, 2);
```

当您调用一个不存在的远程方法时，客户端会抛出一个 `Hyperf\Jet\Exception\ServerException` 异常。

### 通过自定义客户端调用

您可以创建一个 `Hyperf\Jet\AbstractClient` 的子类作为自定义的客户端类，来完成远程方法的调用，比如，您希望定义一个 `CalculatorService` 服务的 `jsonrpc` 协议的客户端类，您可以先定义一个 `CalculatorService` 类，如下所示：

```php
<?php

use Hyperf\Jet\AbstractClient;
use Hyperf\Jet\Packer\JsonEofPacker;
use Hyperf\Jet\Transporter\StreamSocketTransporter;
use Hyperf\Rpc\Contract\DataFormatterInterface;
use Hyperf\Rpc\Contract\PackerInterface;
use Hyperf\Rpc\Contract\PathGeneratorInterface;
use Hyperf\Rpc\Contract\TransporterInterface;

/**
 * @method int add(int $a, int $b);
 */
class CalculatorService extends AbstractClient
{
    // 定义 `CalculatorService` 作为 $service 参数的默认值
    public function __construct(
        string $service = 'CalculatorService',
        TransporterInterface $transporter = null,
        PackerInterface $packer = null,
        ?DataFormatterInterface $dataFormatter = null,
        ?PathGeneratorInterface $pathGenerator = null
    ) {
        // 这里指定 transporter，您仍然可以通过 ProtocolManager 来获得 transporter 或从构造函数传递
        $transporter = new StreamSocketTransporter('127.0.0.1', 9503);
        // 这里指定 packer，您仍然可以通过 ProtocolManager 来获得 packer 或从构造函数传递
        $packer = new JsonEofPacker();
        parent::__construct($service, $transporter, $packer, $dataFormatter, $pathGenerator);
    }
}
```

现在，您可以通过该类来直接调用远程方法了，如下所示：

```php
// 调用远程方法 `add` 并带上参数 `1` 和 `2`
// $result 即为远程方法的返回值
$client = new CalculatorService();
$result = $client->add(1, 2);
```