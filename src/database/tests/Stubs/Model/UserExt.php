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
 * @property int $count
 * @property float $float_num
 * @property string $str
 * @property string $json
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class UserExt extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'user_ext';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'count', 'float_num', 'str', 'json', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'count' => 'integer', 'float_num' => 'float', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
