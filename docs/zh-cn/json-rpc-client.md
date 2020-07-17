# JSON RPC 客户端

> 本扩展只应用于 `FPM` 或其他 `常驻内存` 框架，`Hyperf` 框架请直接使用 `hyperf/json-rpc`

## 安装

```
composer require hyperf/jsonrpc-client
```

## 使用

- 实现 Client

具体方法可以根据 `Server` 端的 `Interface` 进行配置。

```php
<?php

use Hyperf\JsonRpcClient\Client;

/**
 * @method string id(string $id)
 * @method void exception()
 */
class IdGenerator extends Client
{
}

```

- 调用 RPC

根据实际情况，传入 `ServiceName`，`Transporter` 和 `Packer`。

`ServiceName` 传入服务端设置的 `Name` 即可。
`Transporter` 需要传入对端的 `host` 和 `port`，暂不支持 `注册中心`，如果需要动态地址，需要自行处理。
`Packer` 根据服务端的分包规则，进行选择。

```php
<?php

use Hyperf\JsonRpcClient\Packer\JsonLengthPacker;
use Hyperf\JsonRpcClient\Transporter\StreamSocketTransporter;

$client = new IdGenerator('IdGenerateService', new StreamSocketTransporter('127.0.0.1', 9502), new JsonLengthPacker());
$ret = $client->id($id = uniqid());
```
