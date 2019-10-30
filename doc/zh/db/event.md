# 事件
模型事件实现于 [psr/event-dispatcher](https://github.com/php-fig/event-dispatcher) 接口。

## 自定义监听器

得益于 [hyperf/event](https://github.com/hyperf/event) 组件的支撑，用户可以很方便的对以下事件进行监听。
例如 `QueryExecuted` , `StatementPrepared` , `TransactionBeginning` , `TransactionCommitted` , `TransactionRolledBack` 。
接下来我们就实现一个记录SQL的监听器，来说一下怎么使用。
首先我们定义好 `DbQueryExecutedListener` ，实现 `Hyperf\Event\Contract\ListenerInterface` 接口并对类定义 `Hyperf\Event\Annotation\Listener` 注解，这样 Hyperf 就会自动把该监听器注册到事件调度器中，无需任何手动配置，示例代码如下：

```php
<?php

declare(strict_types=1);

namespace App\Listeners;

use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Str;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * @Listener
 */
class DbQueryExecutedListener implements ListenerInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerFactory $loggerFactory)
    {
        $this->logger = $loggerFactory->get('sql');
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

模型事件与 `EloquentORM` 不太一致，`EloquentORM` 使用 `Observer` 监听模型事件。`Hyperf` 直接使用 `钩子函数` 来处理对应的事件。如果你还是喜欢 `Observer` 的方式，可以通过 `事件监听`，自己实现。当然，你也可以在 [issue#2](https://github.com/hyperf/hyperf/issues/2) 下面告诉我们。

### 钩子函数

|    事件名    |     触发实际     | 是否阻断 |               备注                |
|:------------:|:----------------:|:--------:|:-------------------------- --:|
|   booting    |  模型首次加载前  |    否    |    进程生命周期中只会触发一次         |
|    booted    |  模型首次加载后  |    否    |    进程生命周期中只会触发一次         |
|  retrieved   |    填充数据后   |    否    |  每当模型从DB或缓存查询出来后触发      |
|   creating   |    数据创建时   |    是    |                                  |
|   created    |    数据创建后   |    否    |                                  |
|   updating   |    数据更新时   |    是    |                                  |
|   updated    |    数据更新后   |    否    |                                  |
|    saving    | 数据创建或更新时 |    是    |                                  |
|    saved     | 数据创建或更新后 |    否    |                                  |
|  restoring   | 软删除数据回复时 |    是    |                                  |
|   restored   | 软删除数据回复后 |    否    |                                  |
|   deleting   |    数据删除时   |    是    |                                  |
|   deleted    |    数据删除后   |    否    |                                  |
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

当你需要监听所有的模型事件时，可以很方便的自定义对应的 `Listener`，比如下方模型缓存的监听器，当模型修改和删除后，会删除对应缓存。

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

/**
 * @Listener
 */
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