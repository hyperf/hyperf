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
namespace Hyperf\DbConnection\Model;

use Hyperf\Database\Model\Model as BaseModel;
use Hyperf\DbConnection\Traits\HasContainer;
use Hyperf\DbConnection\Traits\HasRepository;

class Model extends BaseModel
{
    use HasContainer;
    use HasRepository;
}
