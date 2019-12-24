# swoole-table进程共享内存

## 介绍

Table一个基于共享内存和锁实现的超高性能，并发数据结构。用于解决多进程/多线程数据共享和同步加锁问题。

## 优势

性能强悍，单线程每秒可读写200万次

应用代码无需加锁，Table内置行锁自旋锁，所有操作均是多线程/多进程安全。用户层完全不需要考虑数据同步问题。

支持多进程，Table可以用于多进程之间共享数据

使用行锁，而不是全局锁，仅当2个进程在同一CPU时间，并发读取同一条数据才会进行发生抢锁

## 配置自定义table格式
```php
<?php

declare(strict_types=1);

namespace App\Service;

class UserTable implements UserTableInterface
{
    /** @var \Swoole\Table */
    private $table;

    public function __construct()
    {
        $table = new \Swoole\Table(1024);
        $table->column('userId', $table::TYPE_INT, 8);
        $table->column('phone', $table::TYPE_STRING, 11);
        $table->create();
        $this->table = $table;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->table, $name], $arguments);
    }
}

```

## 创建一个自定义table

在框架初始子进程时使用注解创建table

```php
<?php

declare(strict_types=1);

namespace App\Process;

use Hyperf\Process\AbstractProcess;

class Process extends AbstractProcess
{
    /**
     * @var bool
     */
    protected $isEnable = true;

    /**
     * @Inject()
     * @var UserTableInterface
     */
    private $userTable;

    public function handle(): void
    {
        $this->isEnable = false;
    }

    public function isEnable(): bool
    {
        return $this->isEnable;
    }
}

```

## 使用自定义table

在hypef服务中都可使用table共享内存

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\Utils\ApplicationContext;

class IndexController
{
    public function index()
    {
        /** @var \Swoole\Table $userTable */
        $userTable = (ApplicationContext::getContainer())->get(UserTableInterface::class);
        
        $userTable->set('user:1', [
            'userId' => 1,
            'phone' =>  1234567890
        ]);
        
        $userTable->get('user:1');
    }
}

```
