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

namespace HyperfTest\Database\Stubs;

use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Prunable;
use function Hyperf\Support\now;

class PrunableModelStub extends Model
{
    use Prunable;

    protected ?string $table = 'prunable_stub';

    protected array $fillable = ['name'];

    public function prunable(): Builder
    {
        return $this->where('created_at', '<=', now()->subMonth());
    }
}