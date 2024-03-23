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

use Hyperf\DbConnection\Model\Model;

class ModelStub extends Model
{
    public array $fillable = ['id', 'json_data', 'str', 'float_num'];

    public array $casts = ['id' => 'integer', 'json_data' => 'json', 'str' => 'string', 'float_num' => 'float'];
}
