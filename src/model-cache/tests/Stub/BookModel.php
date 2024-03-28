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

namespace HyperfTest\ModelCache\Stub;

use Carbon\Carbon;
use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;
use HyperfTest\Database\Stubs\Model\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class BookModel extends Model implements CacheableInterface
{
    use Cacheable;

    /**
     * The table associated with the model.
     */
    protected ?string $table = 'book';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'user_id', 'title', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'user_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(UserModel::class, 'user_id', 'id');
    }

    public function image()
    {
        return $this->morphOne(ImageModel::class, 'imageable');
    }

    public function getCacheTTL(): ?int
    {
        return 100;
    }
}
