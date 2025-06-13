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

use Hyperf\Context\ApplicationContext;
use Hyperf\Database\Model\Builder as EloquentBuilder;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Scope;
use Hyperf\Scout\Event\ModelsFlushed;
use Hyperf\Scout\Event\ModelsImported;
use Psr\EventDispatcher\EventDispatcherInterface;

use function Hyperf\Config\config;

class SearchableScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(EloquentBuilder $builder, Model $model)
    {
    }

    /**
     * Extend the query builder with the needed functions.
     */
    public function extend(EloquentBuilder $builder)
    {
        $builder->macro('searchable', function (EloquentBuilder $builder, $chunk = null, $column = null) {
            $callback = function ($models) {
                $models->filter->shouldBeSearchable()->searchable();
                $dispatcher = ApplicationContext::getContainer()->get(EventDispatcherInterface::class);
                $dispatcher->dispatch(new ModelsImported($models));
            };

            $chunk = $chunk ?: config('scout.chunk.searchable', 500);

            $column ? $builder->chunkById($chunk, $callback, $column) : $builder->chunk($chunk, $callback);
        });
        $builder->macro('unsearchable', function (EloquentBuilder $builder, $chunk = null) {
            $builder->chunk($chunk ?: config('scout.chunk.unsearchable', 500), function ($models) {
                $models->unsearchable();
                $dispatcher = ApplicationContext::getContainer()->get(EventDispatcherInterface::class);
                $dispatcher->dispatch(new ModelsFlushed($models));
            });
        });
    }
}
