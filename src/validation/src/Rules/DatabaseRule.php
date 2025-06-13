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

namespace Hyperf\Validation\Rules;

use Closure;

use function Hyperf\Collection\collect;

trait DatabaseRule
{
    /**
     * The extra where clauses for the query.
     */
    protected array $wheres = [];

    /**
     * The array of custom query callbacks.
     */
    protected array $using = [];

    /**
     * Create a new rule instance.
     *
     * @param string $table the table to run the query against
     * @param string $column the column to check on
     */
    public function __construct(protected string $table, protected string $column = 'NULL')
    {
    }

    /**
     * Set a "where" constraint on the query.
     *
     * @param null|array|string $value
     */
    public function where(Closure|string $column, $value = null): static
    {
        if (is_array($value)) {
            return $this->whereIn($column, $value);
        }

        if ($column instanceof Closure) {
            return $this->using($column);
        }

        $this->wheres[] = compact('column', 'value');

        return $this;
    }

    /**
     * Set a "where not" constraint on the query.
     *
     * @param array|string $value
     */
    public function whereNot(string $column, mixed $value): static
    {
        if (is_array($value)) {
            return $this->whereNotIn($column, $value);
        }

        return $this->where($column, '!' . $value);
    }

    /**
     * Set a "where null" constraint on the query.
     *
     * @return $this
     */
    public function whereNull(string $column): static
    {
        return $this->where($column, 'NULL');
    }

    /**
     * Set a "where not null" constraint on the query.
     *
     * @return $this
     */
    public function whereNotNull(string $column)
    {
        return $this->where($column, 'NOT_NULL');
    }

    /**
     * Set a "where in" constraint on the query.
     *
     * @return $this
     */
    public function whereIn(string $column, array $values)
    {
        return $this->where(function ($query) use ($column, $values) {
            $query->whereIn($column, $values);
        });
    }

    /**
     * Set a "where not in" constraint on the query.
     *
     * @return $this
     */
    public function whereNotIn(string $column, array $values)
    {
        return $this->where(function ($query) use ($column, $values) {
            $query->whereNotIn($column, $values);
        });
    }

    /**
     * Register a custom query callback.
     *
     * @return $this
     */
    public function using(Closure $callback)
    {
        $this->using[] = $callback;

        return $this;
    }

    /**
     * Get the custom query callbacks for the rule.
     */
    public function queryCallbacks(): array
    {
        return $this->using;
    }

    /**
     * Format the where clauses.
     */
    protected function formatWheres(): string
    {
        return collect($this->wheres)->map(fn ($where) => $where['column'] . ',"' . str_replace('"', '""', (string) $where['value']) . '"')->implode(',');
    }
}
