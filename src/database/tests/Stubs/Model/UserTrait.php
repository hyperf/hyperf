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

trait UserTrait
{
    protected function user()
    {
        return $this->belongsTo(User::class, 'id', 'id');
    }
}
