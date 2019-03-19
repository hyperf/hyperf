# 事件
模型事件实现于`psr/event-dispatcher`接口。

## 自定义监听器
得益于`hyperf/event`组件用户可以很方便的对以下事件进行监听。
例如`QueryExecuted`,`StatementPrepared`,`TransactionBeginning`,`TransactionCommitted`,`TransactionRolledBack`。
接下来我们就实现一个记录SQL的监听器，来说一下怎么使用。
首先我们定义好`DbQueryExecutedListener`，实现`Hyperf\Event\Contract\ListenerInterface`接口并加上`Hyperf\Event\Annotation\Listener`注解，这样框架就会自动把监听器注册到事件调度器中，无需任何手动配置，监听事件，具体代码如下。

```php
<?php

declare(strict_types=1);

namespace App\Listeners;

use Hyperf\Utils\Arr;
use Hyperf\Utils\Str;
use Psr\Log\LoggerInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Event\Annotation\Listener;
use Psr\Container\ContainerInterface;
use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Event\Contract\ListenerInterface;

/**
 * @Listener
 */
class DbQueryExecutedListener implements ListenerInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->logger = $container->get(LoggerFactory::class)->get('sql');
    }

    public function listen(): array
    {
        return [
            QueryExecuted::class,
        ];
    }

    /**
     * @param QueryExecuted $event
     */
    public function process(object $event)
    {
        if ($event instanceof QueryExecuted) {
            $sql = $event->sql;
            if (! Arr::isAssoc($event->bindings)) {
                foreach ($event->bindings as $key => $value) {
                    $sql = Str::replaceFirst('?', "'{$value}'", $sql);
                }
            }

            $this->logger->info(sprintf('[%s] %s', $event->time, $sql));
        }
    }
}

```

## 模型事件
