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
namespace Hyperf\DbConnection\Model\Relations;

use Hyperf\Database\Model\Relations\Pivot as BasePivot;
use Hyperf\DbConnection\Traits\HasContainer;

class Pivot extends BasePivot
{
    use HasContainer;
}
