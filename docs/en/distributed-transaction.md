# Distributed Transaction

[dtm-client](https://github.com/dtm-php/dtm-client) is a DTM distributed transaction client component developed and maintained by the Hyperf team. Used in conjunction with DTM-Server, it can implement distributed transaction management and can be stably used in production environments.
[seata/seata-php](https://github.com/seata/seata-php) is a Seata PHP client component developed by the Hyperf team and contributed to the Seata open-source community. Used in conjunction with Seata-Server, it can implement distributed transaction management. It is currently under development and iteration and cannot yet be used in production environments. We hope everyone can participate in accelerating its incubation.

# Introduction to DTM-Client

[dtm/dtm-client](https://packagist.org/packages/dtm/dtm-client) is a PHP client for the distributed transaction manager [DTM](https://github.com/dtm-labs/dtm). It already supports TCC, Saga, XA, and two-phase message distributed transaction modes, and implements communication with DTM Server using either HTTP or gRPC protocols respectively. The client can run safely in PHP-FPM and Swoole coroutine environments, and provides even easier-to-use functional support for [Hyperf](https://github.com/hyperf/hyperf).

# About DTM

DTM is an open-source distributed transaction manager implemented in Go, providing powerful capabilities for cross-language and cross-storage engine combined transactions. DTM elegantly solves difficult problems in distributed transactions such as idempotency, null compensation, and hanging, and also provides a simple-to-use, high-performance, and easily horizontally scalable distributed transaction solution.

## Highlights

* Extremely easy to get started
    - Zero-configuration service startup, provides very simple HTTP interfaces, greatly reducing the difficulty of getting started with distributed transactions.
* Cross-language
    - Suitable for companies with multi-language stacks. Convenient for various languages such as Go, Python, PHP, NodeJs, Ruby, C#, etc.
* Simple to use
    - Developers no longer worry about problems such as hanging, null compensation, or idempotency. Sub-transaction barrier technology is pioneered to handle them on their behalf.
* Easy to deploy and expand
    - Only depends on MySQL/Redis, deployment is simple, easy to cluster, and easy to horizontally expand.
* Support for multiple distributed transaction protocols
    - TCC, SAGA, XA, two-phase message, a one-stop solution to various distributed transaction problems.

## Comparison

For non-Java languages, there is currently no mature distributed transaction manager other than DTM. Therefore, here is a comparison between DTM and Seata, the most mature open-source project in Java:

| Feature | DTM | SEATA | Note |
|:-----:|:----:|:------------------------------------------------------------------------------------------------:|:----:|
| [Supported Languages](https://dtm.pub/other/opensource.html#lang) | <span style="color:green">Go, C#, Java, Python, PHP...</span> | <span style="color:orange">Java, Go</span> | DTM can easily integrate a new language |
| [Storage Engine](https://dtm.pub/other/opensource.html#store) | <span style="color:green"> Supports databases, Redis, Mongo, etc. </span> | <span style="color:orange"> Database </span> | |
| [Exception Handling](https://dtm.pub/other/opensource.html#exception) | <span style="color:green"> Sub-transaction barrier automatic handling </span> | <span style="color:orange"> Manual handling </span> | DTM solves idempotency, hanging, null compensation |
| [SAGA Transaction](https://dtm.pub/other/opensource.html#saga) | <span style="color:green"> Minimalist and easy to use </span> | <span style="color:orange"> Complex state machine </span> | |
| [Two-phase Message](https://dtm.pub/other/opensource.html#msg) | <span style="color:green">✓</span> | <span style="color:red">✗</span> | Simplest message eventual consistency architecture |
| [TCC Transaction](https://dtm.pub/other/opensource.html#tcc) | <span style="color:green">✓</span> | <span style="color:green">✓</span> | |
| [XA Transaction](https://dtm.pub/other/opensource.html#xa) | <span style="color:green">✓</span> | <span style="color:green">✓</span> | |
| [AT Transaction](https://dtm.pub/other/opensource.html#at) | <span style="color:orange"> XA recommended </span> | <span style="color:green">✓</span> | AT is similar to XA, but has dirty rollback |
| [Single Service Multi-Data Source](https://dtm.pub/other/opensource.html#multidb) | <span style="color:green">✓</span> | <span style="color:red">✗</span> | |
| [Communication Protocol](https://dtm.pub/other/opensource.html#protocol) | HTTP, gRPC | Dubbo and other protocols | DTM is more cloud-native friendly |
| [Star Count](https://dtm.pub/other/opensource.html#star) | <img src="https://img.shields.io/github/stars/dtm-labs/dtm.svg?style=social" alt="github stars"/> | <img src="https://img.shields.io/github/stars/seata/seata.svg?style=social" alt="github stars"/> | DTM released version 0.1 on 2021-06-04 and is developing rapidly |

Judging from the features compared above, DTM has great advantages in many aspects. If you consider multi-language support and multi-storage engine support, then DTM is undoubtedly your first choice.

# Installation

dtm-client can be easily installed via Composer:

```bash
composer require dtm/dtm-client
```

* Do not forget to start the DTM Server when using it.

# Configuration

## Configuration File

If you are using it in the Hyperf framework, after installing the component, you can use the following `vendor:publish` command to publish the configuration file to `./config/autoload/dtm.php` in one go:

```bash
php bin/hyperf.php vendor:publish dtm/dtm-client
```

If you are using it in a non-Hyperf framework, you can copy the `./vendor/dtm/dtm-client/publish/dtm.php` file to the corresponding configuration directory.

```php
use DtmClient\Constants\Protocol;
use DtmClient\Constants\DbType;

return [
    // The protocol for communication between client and DTM Server, supports Protocol::HTTP and Protocol::GRPC
    'protocol' => Protocol::HTTP,
    // Address of DTM Server
    'server' => '127.0.0.1',
    // Ports of DTM Server
    'port' => [
        'http' => 36789,
        'grpc' => 36790,
    ],
    // Sub-transaction barrier configuration
    'barrier' => [
        // Sub-transaction barrier configuration under DB mode
        'db' => [
            'type' => DbType::MySQL
        ],
        // Sub-transaction barrier configuration under Redis mode
        'redis' => [
            // Timeout period for sub-transaction barrier records
            'expire_seconds' => 7 * 86400,
        ],
        // Classes to apply sub-transaction barrier in non-Hyperf framework
        'apply' => [],
    ],
    // General configuration for Guzzle client under HTTP protocol
    'guzzle' => [
        'options' => [],
    ],
];
```

## Configure Middleware

Before use, you need to configure the `DtmClient\Middleware\DtmMiddleware` middleware as a global middleware for the Server. This middleware supports the PSR-15 specification and can be applied to various frameworks that support this specification.
For middleware configuration in Hyperf, you can refer to the [Hyperf Middleware](middleware.md) chapter.

# Usage

The usage of dtm-client is very simple. We provide an example project [dtm-php/dtm-sample](https://github.com/dtm-php/dtm-sample) to help you better understand and debug.
Before using this component, it is also strongly recommended that you read the [DTM Official Documentation](https://dtm.pub/) for more detailed understanding.

## TCC Mode

TCC mode is a very popular flexible transaction solution, composed of the acronyms of Try-Confirm-Cancel. It was first proposed by Pat Helland in a paper published in 2007 titled "Life beyond Distributed Transactions: an Apostate’s Opinion".

### 3 Stages of TCC

Try stage: Try to execute, complete all business checks (consistency), and reserve necessary business resources (quasi-isolation).
Confirm stage: If the Try of all branches is successful, it goes to the Confirm stage. Confirm truly executes the business, makes no business checks, and only uses the business resources reserved in the Try stage.
Cancel stage: If one of the Try of all branches fails, it goes to the Cancel stage. Cancel releases the business resources reserved in the Try stage.

If we want to perform a business similar to bank cross-bank transfer, TransOut and TransIn are in different microservices respectively. A typical timing diagram for a successfully completed TCC transaction is as follows:

<img src="https://dtm.pub/assets/tcc_normal.dea14fb3.jpg" height=600 />

### Code Example

The following demonstrates the method of use in the Hyperf framework; other frameworks are similar:

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
                // Create call data for sub-transaction A
                $tcc->callBranch(
                    // Parameters for calling Try method
                    ['amount' => 30],
                    // URL for Try method
                    $this->serviceUri . '/tcc/transA/try',
                    // URL for Confirm method
                    $this->serviceUri . '/tcc/transA/confirm',
                    // URL for Cancel method
                    $this->serviceUri . '/tcc/transA/cancel'
                );
                // Create call data for sub-transaction B, and so on
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
        // Get the global transaction ID via TransContext::getGid() and return it
        return TransContext::getGid();
    }
}
```

## Saga Mode

The Saga mode is one of the most famous solutions in the field of distributed transactions and is also very popular in major systems. It first appeared in a paper published in 1987 by Hector Garcaa-Molrna & Kenneth Salem, [SAGAS](https://www.cs.cornell.edu/andru/cs711/2002fa/reading/sagas.pdf).

Saga is a form of eventual consistency transaction, also a flexible transaction, also called a Long-running-transaction. Saga consists of a series of local transactions. After each local transaction updates the database, it will publish a message or an event to trigger the execution of the next local transaction in the Saga global transaction. If a local transaction fails because certain business rules cannot be met, Saga will execute compensation operations for all transactions successfully submitted before this failed transaction. Therefore, when comparing the Saga mode with the TCC mode, because it lacks the step of resource reservation, implementing rollback logic often becomes more troublesome.

### Saga Sub-transaction Splitting

For example, if we want to perform a business similar to a bank cross-bank transfer, transfer 30 yuan from account A to account B. According to the principle of Saga transaction, we split the entire global transaction into the following services:
- TransOut service, which will subtract 30 yuan from account A.
- TransOutCompensate service, which rolls back the above transfer operation, i.e., add 30 yuan to account A.
- TransIn service, which will add 30 yuan to account B.
- TransInCompensate service, which rolls back the above transfer-in operation, i.e., subtract 30 yuan from account B.

The logic of the entire transaction is:

Transfer-out successful => Transfer-in successful => Global transaction completed

If an error occurs in the middle, such as an error in transferring to account B, the compensation operation of the executed branch will be called, i.e.:

Transfer-out successful => Transfer-in failed => Transfer-in compensation successful => Transfer-out compensation successful => Global transaction rollback completed

The following is a typical timing diagram for a successfully completed SAGA transaction:

<img src="https://dtm.pub/assets/saga_normal.a2849672.jpg" height=428 />

### Code Example

The following demonstrates the method of use in the Hyperf framework; other frameworks are similar:

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
        // Initialize Saga transaction
        $this->saga->init();
        // Add TransOut sub-transaction
        $this->saga->add(
            $this->serviceUri . '/saga/transOut', 
            $this->serviceUri . '/saga/transOutCompensate', 
            $payload
        );
        // Add TransIn sub-transaction
        $this->saga->add(
            $this->serviceUri . '/saga/transIn', 
            $this->serviceUri . '/saga/transInCompensate', 
            $payload
        );
        // Submit Saga transaction
        $this->saga->submit();
        // Get the global transaction ID via TransContext::getGid() and return it
        return TransContext::getGid();
    }
}
```

## XA Mode

XA is a distributed transaction specification proposed by the X/Open organization. The XA specification mainly defines the interface between the (global) transaction manager (TM) and the (local) resource manager (RM). Local databases such as mysql play the RM role in XA.

XA is divided into two phases in total:

Phase 1 (prepare): All participating RMs prepare to execute transactions and lock necessary resources. When the participants are ready, they report to the TM that they are ready. Phase 2 (commit/rollback): After the transaction manager (TM) confirms that all participants (RMs) are ready, it sends a commit command to all participants.

Currently, mainstream databases basically support XA transactions, including mysql, oracle, sqlserver, postgres.

The following is a typical timing diagram for a successfully completed XA transaction:

<img src="https://dtm.pub/assets/xa_normal.5a0ce600.jpg" height=600/>

### Code Example

The following demonstrates the method of use in the Hyperf framework; other frameworks are similar:
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
        // Open XA global transaction
        $gid = $this->xa->generateGid();
        $this->xa->globalTransaction($gid, function () use ($payload) {
            // Call sub-transaction interface
            $respone = $this->xa->callBranch($this->serviceUri . '/xa/api/transIn', $payload);
            // Get sub-transaction return structure under XA http mode
            /* @var ResponseInterface $respone */
            $respone->getBody()->getContents();
            // Call sub-transaction interface
            $payload = ['amount' => 10];
            $this->xa->callBranch($this->serviceUri . '/xa/api/transOut', $payload);
        });
        // Get the global transaction ID via TransContext::getGid() and return it
        return TransContext::getGid();
    }

    #[RequestMapping(methods: ["GET", "POST", "PUT"], path: 'api/transIn')]
    public function transIn(RequestInterface $request): array
    {
        $content = $request->post('amount');
        $amount = $content['amount'] ?? 50;
        // Simulate transIn method under distributed system
        $this->xa->localTransaction(function (DBTransactionInterface $dbTransaction) use ($amount) {
            // Please use DBTransactionInterface to handle local Mysql transaction
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
        // Simulate transOut method under distributed system
        $this->xa->localTransaction(function (DBTransactionInterface $dbTransaction) use ($amount) {
            // Please use DBTransactionInterface to handle local Mysql transaction
            $dbTransaction->xaExecute('UPDATE `order` set `amount` = `amount` - ? where id = 2', [$amount]);
        });

        return ['status' => 0, 'message' => 'ok'];
    }
}
```
The above code first registers a global XA transaction, then adds two sub-transactions, transIn and transOut. After all sub-transactions are executed successfully, they are submitted to dtm. After dtm receives the submitted xa global transaction, it will call xa commit of all sub-transactions to complete the entire xa transaction.
