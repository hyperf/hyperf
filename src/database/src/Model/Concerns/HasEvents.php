<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Database\Model\Concerns;

use Hyperf\Utils\Arr;
use Hyperf\Database\Model\Events\Saved;
use Hyperf\Database\Model\Events\Booted;
use Hyperf\Database\Model\Events\Saving;
use Hyperf\Database\Model\Events\Booting;
use Hyperf\Database\Model\Events\Created;
use Hyperf\Database\Model\Events\Deleted;
use Hyperf\Database\Model\Events\Updated;
use Hyperf\Database\Model\Events\Creating;
use Hyperf\Database\Model\Events\Deleting;
use Hyperf\Database\Model\Events\Restored;
use Hyperf\Database\Model\Events\Updating;
use Hyperf\Database\Model\Events\Restoring;
use Hyperf\Database\Model\Events\Retrieved;
use Hyperf\Database\Model\Events\ForceDeleted;
use Psr\EventDispatcher\StoppableEventInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @method retrieved(Retrieved $event)
 * @method creating(Creating $event)
 * @method created(Created $event)
 * @method updating(Updating $event)
 * @method updated(Updated $event)
 * @method saving(Saving $event)
 * @method saved(Saved $event)
 * @method restoring(Restoring $event)
 * @method restored(Restored $event)
 * @method deleting(Deleting $event)
 * @method deleted(Deleted $event)
 * @method forceDeleted(ForceDeleted $event)
 */
trait HasEvents
{
    /**
     * User exposed events.
     *
     * @var array
     */
    protected $events = [];

    /**
     * User exposed observable events.
     *
     * These are extra user-defined events observers may subscribe to.
     *
     * @var array
     */
    protected static $observables = [];

    /**
     * Register observers with the model.
     *
     * @param  object|array|string  $classes
     * @return void
     */
    public static function observe($classes): void
    {
        $instance = new static;

        foreach (Arr::wrap($classes) as $class) {
            $instance->registerObserver($class);
        }
    }

    /**
     * Clear all registered observers with the model.
     *
     * @return void
     */
    public static function clearObservables(): void
    {
        static::$observables = [];
    }

    /**
     * Register a single observer with the model.
     *
     * @param  object|string $class
     * @return void
     */
    protected function registerObserver($class): void
    {
        $className = is_string($class) ? $class : get_class($class);

        foreach ($this->getDefaultEvents() as $alias => $eventClass) {
            if (method_exists($class, $alias)) {
                static::$observables[static::class][$alias] = $className;
            }
        }
    }

    /**
     * Set the user-defined event names.
     *
     * @return self
     */
    public function setEvents(array $events): self
    {
        foreach ($events as $key => $value) {
            if (is_numeric($key) && is_string($value)) {
                $events[$value] = '';
                unset($events[$key]);
            }
        }

        $this->events = $events;

        return $this;
    }

    /**
     * Add some observable event.
     *
     * @param array|string $events
     * @return void
     */
    public function addEvents($events): void
    {
        $this->events = array_unique(array_merge($this->events, is_array($events) ? $events : func_get_args()));
    }

    /**
     * Remove some registed event.
     *
     * @param array $events
     * @return void
     */
    public function removeEvents(array $events): void
    {
        foreach ($events as $value) {
            if (isset($this->events[$value])) {
                // When passing the key of event.
                unset($this->events[$value]);
            } elseif (class_exists($value) && $key = array_search($value, $this->events)) {
                // When passing the class of event.
                unset($this->events[$key]);
            }
        }
    }

    /**
     * Get the available event names, the custom event will override the default event.
     *
     * @return array [EventMethodName => EventClass]
     */
    public function getAvailableEvents(): array
    {
        return array_replace($this->getDefaultEvents(), $this->events);
    }

    /**
     * Set observable mappings.
     *
     * @param array $observables
     * @return self
     */
    public function setObservables(array $observables): self
    {
        static::$observables[static::class] = $observables;

        return $this;
    }

    /**
     * Get observable mappings.
     *
     * @return array
     */
    public function getObservables(): array
    {
        return static::$observables[static::class] ?? [];
    }

    /**
     * Get the default events of Hyperf Database Model.
     *
     * @return array
     */
    protected function getDefaultEvents(): array
    {
        return [
            'booting' => Booting::class,
            'booted' => Booted::class,
            'retrieved' => Retrieved::class,
            'creating' => Creating::class,
            'created' => Created::class,
            'updating' => Updating::class,
            'updated' => Updated::class,
            'saving' => Saving::class,
            'saved' => Saved::class,
            'restoring' => Restoring::class,
            'restored' => Restored::class,
            'deleting' => Deleting::class,
            'deleted' => Deleted::class,
            'forceDeleted' => ForceDeleted::class,
        ];
    }

    /**
     * Fire the given event for the model.
     *
     * @return null|object|StoppableEventInterface
     */
    protected function fireModelEvent(string $event): ?object
    {
        $dispatcher = $this->getEventDispatcher();
        if (! $dispatcher instanceof EventDispatcherInterface) {
            return null;
        }

        $result = $this->fireCustomModelEvent($event);
        // If custom event does not exist, the fireCustomModelEvent() method will return null.
        if (! is_null($result)) {
            return $result;
        }

        // If the model is not running in Hyperf, then the listener method of model will not bind to the EventDispatcher automatically.
        $eventName = $this->getDefaultEvents()[$event];
        return $dispatcher->dispatch(new $eventName($this, $event));
    }

    /**
     * Fire a custom model event for the given event.
     *
     * @return null|object|StoppableEventInterface
     */
    protected function fireCustomModelEvent(string $event)
    {
        if (! isset($this->events[$event])) {
            return;
        }

        return $this->getEventDispatcher()->dispatch(new $this->events[$event]($this));
    }
}
