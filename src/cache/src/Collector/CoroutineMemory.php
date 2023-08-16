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
use Hyperf\Stringable\Str;
use Hyperf\Support\Traits\StaticInstance;

class CoroutineMemory extends Collection
{
    use StaticInstance;

    public function clear()
    {
        $this->items = [];
    }

    public function clearPrefix(string $prefix)
    {
        foreach ($this->items as $key => $item) {
            if (Str::startsWith($key, $prefix)) {
                unset($this->items[$key]);
            }
        }

        return true;
    }
}
