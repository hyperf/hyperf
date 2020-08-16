# Jet

Jet 是一個統一模型的 RPC 客戶端，內建 JSONRPC 協議的適配，該元件可適用於所有的 PHP 環境，包括 PHP-FPM 和 Swoole 或 Hyperf。（在 Hyperf 環境下，目前仍建議直接使用 `hyperf/json-rpc` 元件來作為客戶端使用）

> 未來還會內建 gRPC 和 Tars 協議。

# 安裝

```bash
composer require hyperf/jet
```

# 快速開始

## 註冊協議

> 註冊協議不是必須的一個步驟，但您可以通過 ProtocolManager 管理所有的協議。

您可以通過 `Hyperf\Jet\ProtocolManager` 類來註冊管理任意的協議，每個協議會包含 Transporter, Packer, DataFormatter and PathGenerator 幾個基本的元件，您可以註冊一個 JSONRPC 協議，如下：

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

## 註冊服務

> 註冊服務不是必須的一個步驟，但您可以通過 ServiceManager 管理所有的服務。

在您往 `Hyperf\Jet\ProtocolManager` 註冊了一個協議之後，您可以通過 `Hyperf\Jet\ServiceManager` 將協議繫結到任意的服務上，如下：

```php
<?php
use Hyperf\Jet\ServiceManager;

// 繫結 CalculatorService 與 jsonrpc 協議，同時設定靜態的節點資訊
ServiceManager::register($service = 'CalculatorService', $protocol = 'jsonrpc', [
    ServiceManager::NODES => [
        [$host = '127.0.0.1', $port = 9503],
    ],
]);
```

## 呼叫 RPC 方法

### 通過 ClientFactory 呼叫

在您註冊完協議與服務之後，您可以通過 `Hyperf/Jet/ClientFactory` 來獲得您的服務的客戶端，如下所示：

```php
<?php
use Hyperf\Jet\ClientFactory;

$clientFactory = new ClientFactory();
$client = $clientFactory->create($service = 'CalculatorService', $protocol = 'jsonrpc');
```

當您擁有 client 物件後，您可以通過該物件呼叫任意的遠端方法，如下：

```php
// 呼叫遠端方法 `add` 並帶上引數 `1` 和 `2`
// $result 即為遠端方法的返回值
$result = $client->add(1, 2);
```

當您呼叫一個不存在的遠端方法時，客戶端會丟擲一個 `Hyperf\Jet\Exception\ServerException` 異常。

### 通過自定義客戶端呼叫

您可以建立一個 `Hyperf\Jet\AbstractClient` 的子類作為自定義的客戶端類，來完成遠端方法的呼叫，比如，您希望定義一個 `CalculatorService` 服務的 `jsonrpc` 協議的客戶端類，您可以先定義一個 `CalculatorService` 類，如下所示：

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
    // 定義 `CalculatorService` 作為 $service 引數的預設值
    public function __construct(
        string $service = 'CalculatorService',
        TransporterInterface $transporter = null,
        PackerInterface $packer = null,
        ?DataFormatterInterface $dataFormatter = null,
        ?PathGeneratorInterface $pathGenerator = null
    ) {
        // 這裡指定 transporter，您仍然可以通過 ProtocolManager 來獲得 transporter 或從建構函式傳遞
        $transporter = new StreamSocketTransporter('127.0.0.1', 9503);
        // 這裡指定 packer，您仍然可以通過 ProtocolManager 來獲得 packer 或從建構函式傳遞
        $packer = new JsonEofPacker();
        parent::__construct($service, $transporter, $packer, $dataFormatter, $pathGenerator);
    }
}
```

現在，您可以通過該類來直接呼叫遠端方法了，如下所示：

```php
// 呼叫遠端方法 `add` 並帶上引數 `1` 和 `2`
// $result 即為遠端方法的返回值
$client = new CalculatorService();
$result = $client->add(1, 2);
```