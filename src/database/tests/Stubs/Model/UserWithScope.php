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

namespace HyperfTest\Database\Stubs\Model;

use Hyperf\Database\Model\Builder;

class UserWithScope extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'user';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = [];

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include users of a given type.
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include popular users.
     */
    public function scopePopular(Builder $query): Builder
    {
        return $query->where('votes', '>', 100);
    }

    /**
     * Scope a query to only include users with a specific status.
     */
    public function scopeWithStatus(Builder $query, string $status = 'active'): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include users within an age range.
     */
    public function scopeByAge(Builder $query, int $minAge, ?int $maxAge = null): Builder
    {
        $query = $query->where('age', '>=', $minAge);
        if ($maxAge !== null) {
            $query = $query->where('age', '<=', $maxAge);
        }
        return $query;
    }
}
