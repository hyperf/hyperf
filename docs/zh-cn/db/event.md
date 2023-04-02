# 事件

模型事件实现 [psr/event-dispatcher](https://github.com/php-fig/event-dispatcher) 接口，默认由 [hyperf/event](https://github.com/hyperf/event) 组件提供功能支持。

## 运行事件

在 ORM 的运行期间，会对应触发以下事件，您可以进行对这些事件进行监听以满足您的需求。

| 事件  | 描述 |
| :--------: | :----: |
| Hyperf\Database\Events\QueryExecuted| Query 语句执行后 |
| Hyperf\Database\Events\StatementPrepared| SQL 语句 prepared 后 |
| Hyperf\Database\Events\TransactionBeginning| 事务开启后 |
| Hyperf\Database\Events\TransactionCommitted| 事务提交后 |
| Hyperf\Database\Events\TransactionRolledBack| 事务回滚后 |

### SQL 执行监听器

根据上述的 ORM 运行事件，接下来我们来实现一个记录 SQL 的监听器，达到在每次执行 SQL 时记录下来并输出到日志上。
首先我们定义好 `DbQueryExecutedListener` ，实现 `Hyperf\Event\Contract\ListenerInterface` 接口并对类定义 `Hyperf\Event\Annotation\Listener` 注解，这样 Hyperf 就会在应用启动时自动把该监听器注册到事件调度器中，并在事件触发时执行监听逻辑，示例代码如下：

```php
<?php

declare(strict_types=1);

namespace App\Listener;

use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Collection\Arr;
use Hyperf\Utils\Str;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

#[Listener]
class DbQueryExecutedListener implements ListenerInterface
{
    private LoggerInterface $logger;

    public function __construct(ContainerInterface $container)
    {
        // 输出到对应名为 sql 的日志 name，如不存在则需自行添加配置
        // 这里的 sql 日志 name 不是必须的，只是表达可以将 SQL 执行日志与普通日志区分开
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

模型事件与 `Eloquent ORM` 不太一致，`Eloquent ORM` 使用 `Observer` 监听模型事件。`Hyperf` 提供 `钩子函数` 和 `事件监听` 两种形式来处理对应的事件。

### 钩子函数

|    事件名    |     触发实际     | 是否阻断 |               备注                |
|:------------:|:----------------:|:--------:|:-------------------------- --:|
|   booting    |  模型首次加载前  |    否    |    进程生命周期中只会触发一次         |
|    booted    |  模型首次加载后  |    否    |    进程生命周期中只会触发一次         |
|  retrieved   |    填充数据后   |    否    |  每当模型从 DB 或缓存查询出来后触发      |
|   creating   |    数据创建时   |    是    |                                  |
|   created    |    数据创建后   |    否    |                                  |
|   updating   |    数据更新时   |    是    |                                  |
|   updated    |    数据更新后   |    否    |                                  |
|    saving    | 数据创建或更新时 |    是    |                                  |
|    saved     | 数据创建或更新后 |    否    |                                  |
|  restoring   | 软删除数据恢复时 |    是    |                                  |
|   restored   | 软删除数据恢复后 |    否    |                                  |
|   deleting   |    数据删除时   |    是    |                                  |
|   deleted    |    数据删除后   |    否    |                                  |
| forceDeleting |  数据强制删除时  |    是    |                                  |
| forceDeleted |  数据强制删除后  |    否    |                                  |

针对某个模型的事件使用十分简单，只需要在模型中增加对应的方法即可。例如下方保存数据时，触发 `saving` 事件，主动覆写 `created_at` 字段。

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Hyperf\Database\Model\Events\Saving;

/**
 * @property $id
 * @property $name
 * @property $gender
 * @property $created_at
 * @property $updated_at
 */
class User extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'name', 'gender', 'created_at', 'updated_at'];

    protected $casts = ['id' => 'integer', 'gender' => 'integer'];

    public function saving(Saving $event)
    {
        $this->setCreatedAt('2019-01-01');
    }
}

```

### 事件监听

当您需要监听所有的模型事件时，可以很方便的自定义对应的 `Listener`，比如下方模型缓存的监听器，当模型修改和删除后，会删除对应缓存。

```php
<?php

declare(strict_types=1);

namespace Hyperf\ModelCache\Listener;

use Hyperf\Database\Model\Events\Deleted;
use Hyperf\Database\Model\Events\Event;
use Hyperf\Database\Model\Events\Saved;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\ModelCache\CacheableInterface;

#[Listener]
class DeleteCacheListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            Deleted::class,
            Saved::class,
        ];
    }

    public function process(object $event)
    {
        if ($event instanceof Event) {
            $model = $event->getModel();
            if ($model instanceof CacheableInterface) {
                $model->deleteCache();
            }
        }
    }
}

```
