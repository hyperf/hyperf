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
 * @property string $url
 * @property int $imageable_id
 * @property string $imageable_type
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ImageModel extends Model implements CacheableInterface
{
    use Cacheable;

    /**
     * The table associated with the model.
     */
    protected ?string $table = 'images';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'url', 'imageable_id', 'imageable_type', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'imageable_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    public function imageable()
    {
        return $this->morphTo();
    }
}
