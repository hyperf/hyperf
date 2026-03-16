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

use Hyperf\Database\Model\Concerns\HasUuids;
use Hyperf\Database\Model\Model;

class ModelStubWithUuid extends Model
{
    use HasUuids;

    protected ?string $table = 'stub';

    protected string $primaryKey = 'id';
}
