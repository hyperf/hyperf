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

namespace Hyperf\Scout\Event;

use Hyperf\Database\Model\Collection;
use Hyperf\Database\Model\Model;

class ModelsImported
{
    /**
     * @param Collection<int, Model> $models
     */
    public function __construct(public Collection $models)
    {
    }
}
