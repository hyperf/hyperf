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

use Hyperf\Database\Model\Concerns\HasVersion4Uuids;
use Hyperf\Database\Model\Model;

class ModelStubWithVersion4Uuid extends Model
{
    use HasVersion4Uuids;

    protected ?string $table = 'stub';

    protected string $primaryKey = 'id';
}
