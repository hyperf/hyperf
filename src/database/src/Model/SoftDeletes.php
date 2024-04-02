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

namespace Hyperf\Database\Model;

use Psr\EventDispatcher\StoppableEventInterface;

use function Hyperf\Tappable\tap;

/**
 * @method static static|\Hyperf\Database\Model\Builder|\Hyperf\Database\Query\Builder restoreOrCreate(array $attributes = [], array $values = [])
 * @method static static|\Hyperf\Database\Model\Builder|\Hyperf\Database\Query\Builder withTrashed(bool $withTrashed = true)
 * @method static static|\Hyperf\Database\Model\Builder|\Hyperf\Database\Query\Builder onlyTrashed()
 * @method static static|\Hyperf\Database\Model\Builder|\Hyperf\Database\Query\Builder withoutTrashed()
 */
trait SoftDeletes
{
    /**
     * Indicates if the model is currently force deleting.
     */
    protected bool $forceDeleting = false;

    /**
     * Boot the soft deleting trait for a model.
     */
    public static function bootSoftDeletes()
    {
        static::addGlobalScope(new SoftDeletingScope());
    }

    /**
     * Force a hard delete on a soft deleted model.
     *
     * @return null|bool
     */
    public function forceDelete()
    {
        if ($event = $this->fireModelEvent('forceDeleting')) {
            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                return false;
            }
        }

        $this->forceDeleting = true;

        return tap($this->delete(), function ($deleted) {
            $this->forceDeleting = false;

            if ($deleted) {
                $this->fireModelEvent('forceDeleted');
            }
        });
    }

    /**
     * Restore a soft-deleted model instance.
     *
     * @return null|bool
     */
    public function restore()
    {
        // If the restoring event does not return false, we will proceed with this
        // restore operation. Otherwise, we bail out so the developer will stop
        // the restore totally. We will clear the deleted timestamp and save.
        if ($event = $this->fireModelEvent('restoring')) {
            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                return false;
            }
        }

        $this->{$this->getDeletedAtColumn()} = null;

        // Once we have saved the model, we will fire the "restored" event so this
        // developer will do anything they need to after a restore operation is
        // totally finished. Then we will return the result of the save call.
        $this->exists = true;

        $result = $this->save();

        $this->fireModelEvent('restored');

        return $result;
    }

    /**
     * Determine if the model instance has been soft-deleted.
     *
     * @return bool
     */
    public function trashed()
    {
        return ! is_null($this->{$this->getDeletedAtColumn()});
    }

    /**
     * Determine if the model is currently force deleting.
     *
     * @return bool
     */
    public function isForceDeleting()
    {
        return $this->forceDeleting;
    }

    /**
     * Get the name of the "deleted at" column.
     *
     * @return string
     */
    public function getDeletedAtColumn()
    {
        return defined('static::DELETED_AT') ? static::DELETED_AT : 'deleted_at';
    }

    /**
     * Get the fully qualified "deleted at" column.
     *
     * @return string
     */
    public function getQualifiedDeletedAtColumn()
    {
        return $this->qualifyColumn($this->getDeletedAtColumn());
    }

    /**
     * Perform the actual delete query on this model instance.
     */
    protected function performDeleteOnModel()
    {
        if ($this->forceDeleting) {
            $this->exists = false;

            return $this->newModelQuery()->where($this->getKeyName(), $this->getKey())->forceDelete();
        }

        return $this->runSoftDelete();
    }

    /**
     * Perform the actual delete query on this model instance.
     */
    protected function runSoftDelete()
    {
        $query = $this->newModelQuery()->where($this->getKeyName(), $this->getKey());

        $time = $this->freshTimestamp();

        $columns = [$this->getDeletedAtColumn() => $this->fromDateTime($time)];

        $this->{$this->getDeletedAtColumn()} = $time;

        if ($this->timestamps && ! is_null($this->getUpdatedAtColumn())) {
            $this->{$this->getUpdatedAtColumn()} = $time;

            $columns[$this->getUpdatedAtColumn()] = $this->fromDateTime($time);
        }

        $query->update($columns);
    }
}
