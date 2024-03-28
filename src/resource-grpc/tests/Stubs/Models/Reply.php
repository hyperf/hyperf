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

namespace HyperfTest\ResourceGrpc\Stubs\Models;

use Hyperf\Database\Model\Model;

class Reply extends Model
{
    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [];
}
