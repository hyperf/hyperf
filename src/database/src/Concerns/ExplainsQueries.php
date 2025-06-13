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

namespace Hyperf\Database\Concerns;

use Hyperf\Collection\Collection;
use Hyperf\Database\Query\Builder;

/**
 * @mixin  Builder
 */
trait ExplainsQueries
{
    /**
     * Explains the query.
     */
    public function explain(): Collection
    {
        $sql = $this->toSql();

        $bindings = $this->getBindings();

        $explanation = $this->getConnection()->select('EXPLAIN ' . $sql, $bindings);

        return new Collection($explanation);
    }
}
