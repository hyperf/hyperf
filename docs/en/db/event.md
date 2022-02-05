# Event
Model events are implemented in the [psr/event-dispatcher](https://github.com/php-fig/event-dispatcher) interface.

## Custom Listener

Thanks to the support of the [hyperf/event](https://github.com/hyperf-cloud/event) component, users can easily monitor the following events.
For example `QueryExecuted` , `StatementPrepared` , `TransactionBeginning` , `TransactionCommitted` , `TransactionRolledBack` .
Next, we will implement a listener that records SQL and talk about how to use it.
First, we define `DbQueryExecutedListener`, implement the `Hyperf\Event\Contract\ListenerInterface` interface and define the `Hyperf\Event\Annotation\Listener` annotation on the class, so that Hyperf will automatically register the listener to the event scheduler, Without any manual configuration, the sample code is as follows:

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

## Model Events

Model events are not consistent with `EloquentORM`, which uses `Observer` to listen for model events. `Hyperf` directly uses `hooks` to handle corresponding events. If you still like the way of `Observer`, you can implement `event listener` by yourself. Of course, you can also let us know under [issue#2](https://github.com/hyperf-cloud/hyperf/issues/2).

### Hook Function

| Event name | Trigger actual | Whether to block | Remarks |
|:------------:|:----------------:|:------------:|:--- ----------------------- --:|
| booting | Before the model is first loaded | No | Only fired once in the process life cycle |
| booted | After the model is first loaded | No | Fired only once during the lifetime of the process |
| retrieved | After data is populated | No | Fired whenever the model is queried from the DB or cache |
| creating | when the data was created | yes | |
| created | After data is created | No | |
| updating | When the data is updated | yes | |
| updated | After data update | No | |
| saving | when data is created or updated | yes | |
| saved | After data creation or update | no | |
| restoring | When restoring soft deleted data | Yes | |
| restored | After soft delete data recovery | No | |
| deleting | when data is deleted | yes | |
| deleted | After data is deleted | No | |
| forceDeleted | After data is forcibly deleted | No | |

The use of events for a model is very simple, just add the corresponding method to the model. For example, when the data is saved below, the `saving` event is triggered, and the `created_at` field is actively overwritten.

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

### Event Listener

When you need to monitor all model events, you can easily customize the corresponding `Listener`, such as the listener of the model cache below. When the model is modified and deleted, the corresponding cache will be deleted.

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
