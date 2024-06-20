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

class TestGenerateIdeModel extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'test_generate_ide_models';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'user_id', 'title', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'user_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    public function image()
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function scopeOptionNull($query, ?string $test)
    {
    }

    public function scopeString($query, string $test)
    {
    }

    public function scopeUnion($query, int $appId, int|string $uid)
    {
    }

    public function scopeUnionOrNull($query, int $appId, null|int|string $uid)
    {
    }

    public function scopeSingleOrNull($query, ?string $test)
    {
    }

    // TODO PHP 8.2 起单独支持null类型
}
