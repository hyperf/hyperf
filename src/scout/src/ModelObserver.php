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

namespace Hyperf\Scout;

use Hyperf\Context\Context;
use Hyperf\Database\Model\Events\Deleted;
use Hyperf\Database\Model\Events\ForceDeleted;
use Hyperf\Database\Model\Events\Restored;
use Hyperf\Database\Model\Events\Saved;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\SoftDeletes;

use function Hyperf\Config\config;
use function Hyperf\Support\class_uses_recursive;

class ModelObserver
{
    /**
     * Enable syncing for the given class.
     */
    public static function enableSyncingFor(string $class): void
    {
        Context::override('syncing_disabled', function ($syncingDisabled) use ($class) {
            unset($syncingDisabled[$class]);
            return $syncingDisabled;
        });
    }

    /**
     * Disable syncing for the given class.
     */
    public static function disableSyncingFor(string $class): void
    {
        Context::override('syncing_disabled', function ($syncingDisabled) use ($class) {
            $syncingDisabled[$class] = true;
            return $syncingDisabled;
        });
    }

    /**
     * Determine if syncing is disabled for the given class or model.
     *
     * @param object|string $class
     */
    public static function syncingDisabledFor($class): bool
    {
        $class = is_object($class) ? get_class($class) : $class;
        $syncingDisabled = (array) Context::get('syncing_disabled', []);
        return array_key_exists($class, $syncingDisabled);
    }

    /**
     * Handle the saved event for the model.
     */
    public function saved(Saved $event): void
    {
        /** @var SearchableInterface $model */
        $model = $event->getModel();

        if (static::syncingDisabledFor($model)) {
            return;
        }

        if (! $model->shouldBeSearchable()) {
            $model->unsearchable();
            return;
        }
        $model->searchable();
    }

    /**
     * Handle the deleted event for the model.
     */
    public function deleted(Deleted $event)
    {
        /** @var Model|SearchableInterface $model */
        $model = $event->getModel();

        if (static::syncingDisabledFor($model)) {
            return;
        }
        if ($this->usesSoftDelete($model) && config('scout.soft_delete', false)) {
            $this->saved(new Saved($model));
        } else {
            $model->unsearchable();
        }
    }

    /**
     * Handle the force deleted event for the model.
     */
    public function forceDeleted(ForceDeleted $event)
    {
        /** @var Model|SearchableInterface $model */
        $model = $event->getModel();

        if (static::syncingDisabledFor($model)) {
            return;
        }
        $model->unsearchable();
    }

    /**
     * Handle the restored event for the model.
     */
    public function restored(Restored $event)
    {
        $model = $event->getModel();
        $this->saved(new Saved($model));
    }

    /**
     * Determine if the given model uses soft deletes.
     */
    protected function usesSoftDelete(Model $model): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive($model));
    }
}
