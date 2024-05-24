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

namespace Hyperf\Validation\Rules;

use BackedEnum;
use Hyperf\Contract\Arrayable;
use Stringable;
use UnitEnum;

class ArrayRule implements Stringable
{
    protected $keys;

    public function __construct(
        $keys = null,
    ) {
        if ($keys instanceof Arrayable) {
            $keys = $keys->toArray();
        }
        $this->keys = is_array($keys) ? $keys : func_get_args();
    }

    public function __toString()
    {
        if (empty($this->keys)) {
            return 'array';
        }

        $keys = array_map(
            static fn ($key) => match (true) {
                $key instanceof BackedEnum => $key->value,
                $key instanceof UnitEnum => $key->name,
                default => $key,
            },
            $this->keys,
        );

        return 'array:' . implode(',', $keys);
    }
}
