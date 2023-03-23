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

/**
 * @property int $id
 * @property int $user_id
 * @property int $uid
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class TestModel extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'test';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'user_id', 'uid', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'user_id' => 'integer', 'uid' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    public function image()
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
