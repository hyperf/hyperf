<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Database\Model;

interface Scope
{
    /**
     * Apply the scope to a given Model query builder.
     *
     * @param \Hyperf\Database\Model\Builder $builder
     * @param \Hyperf\Database\Model\Model $model
     */
    public function apply(Builder $builder, Model $model);
}
