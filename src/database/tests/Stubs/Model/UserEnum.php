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

use Carbon\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property Gender $gender
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class UserEnum extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'user';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'name', 'gender', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'gender' => Gender::class, 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    public function book()
    {
        var_dump(1);
        return $this->hasOne(Book::class, 'user_id', 'id'); // ignore
    }
}
