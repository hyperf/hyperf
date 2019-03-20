# 模型

模型组件衍生于 [Eloquent ORM](https://laravel.com/docs/5.8/eloquent)，相关操作均可参考 Eloquent ORM 的文档。

## 创建模型

Hyperf 提供了创建模型的命令，您可以很方便的根据数据表创建对应模型。命令通过 `AST` 生成模型，所以当您增加了某些方法后，也可以使用脚本方便的重置模型。

```
$ php bin/hyperf.php db:model table_name
```

创建的模型如下
```php
<?php

declare(strict_types=1);

namespace App\Models;

use Hyperf\DbConnection\Model\Model;

/**
 * @property $id
 * @property $name
 * @property $sex
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
    protected $fillable = ['id', 'name', 'sex', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'sex' => 'integer'];
}
```

## 模型参数

|    参数    |  类型   |  默认值 |         备注        |
|:----------:|:------:|:------:|:------------------:|
|   table    | string |   无   |  模型对应的table名   |
| primaryKey | string |  'id'  |       模型主键      |
|  fillable  | array  |   []   | 允许被批量复制的属性  |
|   casts    | string |   无   |    数据格式化配置    |
| timestamps |  bool  |  true  |  是否自动维护时间戳   |

## 模型查询

```php
use App\Models\User;

/** @var User $user */
$user = User::query()->where('id', 1)->first();
$user->name = 'Hyperf';
$user->save();

```


## 模型事件

模型事件实现于 `psr/event-dispatcher` 接口，默认情况下会由 [hyperf-cloud/event](https://github.com/hyperf-cloud/event) 组件来提供事件管理器的支持。

### 自定义监听器

得益于 `hyperf/event` 组件用户可以很方便的对以下事件进行监听。
例如 `QueryExecuted` , `StatementPrepared` , `TransactionBeginning` , `TransactionCommitted` , `TransactionRolledBack`。
接下来我们就实现一个记录SQL的监听器，来演示一下怎么使用。
首先我们定义好 `DbQueryExecutedListener` ，实现 `Hyperf\Event\Contract\ListenerInterface` 接口并加上 `Hyperf\Event\Annotation\Listener` 注解，这样框架就会自动把监听器注册到事件调度器中，无需任何手动配置，监听事件，具体代码如下。

```php
<?php

declare(strict_types=1);

namespace App\Listeners;

use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Event\Annotation\Listener;
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

