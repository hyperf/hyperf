<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Database\Model\Concerns;

use Hyperf\Database\Model\Events\Booted;
use Hyperf\Database\Model\Events\Booting;
use Hyperf\Database\Model\Events\Created;
use Hyperf\Database\Model\Events\Creating;
use Hyperf\Database\Model\Events\Deleted;
use Hyperf\Database\Model\Events\Deleting;
use Hyperf\Database\Model\Events\ForceDeleted;
use Hyperf\Database\Model\Events\Restored;
use Hyperf\Database\Model\Events\Restoring;
use Hyperf\Database\Model\Events\Retrieved;
use Hyperf\Database\Model\Events\Saved;
use Hyperf\Database\Model\Events\Saving;
use Hyperf\Database\Model\Events\Updated;
use Hyperf\Database\Model\Events\Updating;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\StoppableEventInterface;

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
     * Set the user-defined event names.
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
     */
    public function addEvents($events): void
    {
        $this->events = array_unique(array_merge($this->events, is_array($events) ? $events : func_get_args()));
    }

    /**
     * Remove some registered event.
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
     * Get the default events of Hyperf Database Model.
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
