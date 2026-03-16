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

namespace Hyperf\Validation;

use Closure;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Query\Builder;
use Hyperf\Stringable\Str;
use Hyperf\Validation\Contract\PresenceVerifierInterface;

class DatabasePresenceVerifier implements PresenceVerifierInterface
{
    /**
     * The database connection to use.
     *
     * @var string
     */
    protected $connection;

    /**
     * Create a new database presence verifier.
     *
     * @param ConnectionResolverInterface $db the database connection instance
     */
    public function __construct(protected ConnectionResolverInterface $db)
    {
    }

    /**
     * Count the number of objects in a collection having the given value.
     *
     * @param string $value
     * @param null|int $excludeId
     * @param null|string $idColumn
     */
    public function getCount(string $collection, string $column, $value, $excludeId = null, $idColumn = null, array $extra = []): int
    {
        $query = $this->table($collection)->where($column, '=', $value);

        if (! is_null($excludeId) && $excludeId !== 'NULL') {
            $query->where($idColumn ?: 'id', '<>', $excludeId);
        }

        return $this->addConditions($query, $extra)->count();
    }

    /**
     * Count the number of objects in a collection with the given values.
     */
    public function getMultiCount(string $collection, string $column, array $values, array $extra = []): int
    {
        $query = $this->table($collection)->whereIn($column, $values);

        return $this->addConditions($query, $extra)->distinct()->count($column);
    }

    /**
     * Get a query builder for the given table.
     *
     * @return Builder
     */
    public function table(string $table)
    {
        return $this->db->connection($this->connection)->table($table)->useWritePdo();
    }

    /**
     * Set the connection to be used.
     */
    public function setConnection(?string $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Add the given conditions to the query.
     *
     * @param Builder $query
     * @return Builder
     */
    protected function addConditions($query, array $conditions)
    {
        foreach ($conditions as $key => $value) {
            if ($value instanceof Closure) {
                $query->where(function ($query) use ($value) {
                    $value($query);
                });
            } else {
                $this->addWhere($query, $key, $value);
            }
        }

        return $query;
    }

    /**
     * Add a "where" clause to the given query.
     *
     * @param Builder $query
     * @param string $extraValue
     */
    protected function addWhere($query, string $key, $extraValue)
    {
        if ($extraValue === 'NULL') {
            $query->whereNull($key);
        } elseif ($extraValue === 'NOT_NULL') {
            $query->whereNotNull($key);
        } elseif (Str::startsWith((string) $extraValue, '!')) {
            $query->where($key, '!=', mb_substr($extraValue, 1));
        } else {
            $query->where($key, $extraValue);
        }
    }
}
