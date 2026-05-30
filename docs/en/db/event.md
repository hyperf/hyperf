# Events

Model events implement the [psr/event-dispatcher](https://github.com/php-fig/event-dispatcher) interface, and are supported by the [hyperf/event](https://github.com/hyperf/event) component by default.

## Operation Events

During the operation of the ORM, the following events will be triggered. You can listen to these events to meet your requirements.

| Event | Description |
| :--------: | :----: |
| Hyperf\Database\Events\QueryExecuted | After a query statement is executed |
| Hyperf\Database\Events\StatementPrepared | After a SQL statement is prepared |
| Hyperf\Database\Events\TransactionBeginning | After a transaction begins |
| Hyperf\Database\Events\TransactionCommitted | After a transaction is committed |
| Hyperf\Database\Events\TransactionRolledBack | After a transaction is rolled back |

### SQL Execution Listener

Based on the ORM operation events mentioned above, let's implement a listener that records SQL statements. This allows us to log SQL statements every time they are executed.
First, we define `DbQueryExecutedListener`, implement the `Hyperf\Event\Contract\ListenerInterface` interface, and apply the `Hyperf\Event\Annotation\Listener` annotation to the class. This way, Hyperf will automatically register this listener to the event dispatcher when the application starts, and execute the listening logic when the event is triggered. The example code is as follows:

```php
<?php

declare(strict_types=1);

namespace App\Listener;

use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Collection\Arr;
use Hyperf\Stringable\Str;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

#[Listener]
class DbQueryExecutedListener implements ListenerInterface
{
    private LoggerInterface $logger;

    public function __construct(ContainerInterface $container)
    {
        // Output to the log named 'sql'. If it doesn't exist, you need to add the configuration yourself.
        // The 'sql' log name is not required; it's just used here to distinguish SQL execution logs from regular logs.
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

## Model Events

Model events in Hyperf are slightly different from `Eloquent ORM`. `Eloquent ORM` uses `Observer` to listen to model events. `Hyperf` provides both `hook functions` and `event listeners` to handle corresponding events.

### Hook Functions

| Event Name | Trigger Time | Blockable | Remarks |
| :------------: | :----------------: | :--------: | :-------------------------- --: |
| booting | Before the model is first loaded | No | Triggered only once in the process lifecycle |
| booted | After the model is first loaded | No | Triggered only once in the process lifecycle |
| retrieved | After data is populated | No | Triggered whenever a model is queried from DB or cache |
| creating | When data is being created | Yes | |
| created | After data is created | No | |
| updating | When data is being updated | Yes | |
| updated | After data is updated | No | |
| saving | When data is being created or updated | Yes | |
| saved | After data is created or updated | No | |
| restoring | When soft-deleted data is being restored | Yes | |
| restored | After soft-deleted data is restored | No | |
| deleting | When data is being deleted | Yes | |
| deleted | After data is deleted | No | |
| forceDeleting | When data is being force-deleted | Yes | |
| forceDeleted | After data is force-deleted | No | |

Using events for a specific model is very simple; just add the corresponding method to the model. For example, when saving data, triggering the `saving` event to proactively overwrite the `created_at` field:

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

When you need to listen to all model events, you can easily define the corresponding `Listener`. For example, the model cache listener below deletes the corresponding cache after the model is modified or deleted.

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

### Observer

Thanks to the [hyperf/model-listener](https://github.com/hyperf/blob/master/src/model-listener/) component, we can also use `Observer` to listen to model events.
Through the [ModelListener](https://github.com/hyperf/hyperf/blob/master/src/model-listener/src/Annotation/ModelListener.php) annotation, we can easily define an observer. The example code is as follows:

```php
<?php
use Hyperf\ModelListener\Annotation\ModelListener;
use App\Model\User;
use Hyperf\Database\Model\Events\Creating;
use Hyperf\Database\Model\Events\Created;

/**
 * Define a UserObserver to listen to User model events.
 * You can also listen to multiple models by passing them in the models attribute.
 * Note that this class will be automatically registered in the container as a singleton.
 */
#[ModelListener(models: [ User::class ])]
class UserObserver
{
    public function creating(Creating $event)
    {
        $user = $event->getModel();
        // Triggered when creating a user
    }
    
    public function created(Created $event)
    {
        $user = $event->getModel();
        // Triggered after the user is created
    }
    
    //... Other events omitted
}
```
