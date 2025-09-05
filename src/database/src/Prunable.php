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

namespace Hyperf\Database;

use Hyperf\Database\Events\ModelsPruned;
use Hyperf\Database\Model\Builder;
use LogicException;

/**
 * @mixin \Hyperf\Database\Model\Model
 */
trait Prunable
{
    /**
     * Prune all prunable models in the database.
     */
    public function pruneAll(int $chunkSize = 1000): int
    {
        $total = 0;

        $this->prunable()
            ->when(static::isSoftDeletable(), function ($query) {
                $query->withTrashed();
            })->chunkById($chunkSize, function ($models) use (&$total) {
                $models->each(function ($model) use (&$total) {
                    $model->prune();

                    ++$total;
                });
                $this->getEventDispatcher()->dispatch(new ModelsPruned(static::class, $total));
            });

        return $total;
    }

    /**
     * Get the prunable model query.
     *
     * @return Builder<static>
     */
    public function prunable(): Builder
    {
        throw new LogicException('Please implement the prunable method on your model.');
    }

    /**
     * Prune the model in the database.
     */
    public function prune(): ?bool
    {
        $this->pruning();

        return static::isSoftDeletable()
            ? $this->forceDelete()
            : $this->delete();
    }

    /**
     * Prepare the model for pruning.
     */
    protected function pruning(): void
    {
    }
}
