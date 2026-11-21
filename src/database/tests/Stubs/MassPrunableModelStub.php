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
use Hyperf\Database\Model\MassPrunable;
use Hyperf\Database\Model\Model;

use function Hyperf\Support\now;

class MassPrunableModelStub extends Model
{
    use MassPrunable;

    protected ?string $table = 'mass_prunable_stub';

    protected array $fillable = ['name'];

    public function prunable(): Builder
    {
        return $this->where('created_at', '<=', now()->subMonth());
    }
}
