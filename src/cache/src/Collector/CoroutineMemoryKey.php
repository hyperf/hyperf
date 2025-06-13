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

namespace Hyperf\Cache\Collector;

use Hyperf\Collection\Collection;
use Hyperf\Support\Traits\StaticInstance;

class CoroutineMemoryKey extends Collection
{
    use StaticInstance;
}
