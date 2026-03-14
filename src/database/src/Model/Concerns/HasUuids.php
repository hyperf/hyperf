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

namespace Hyperf\Database\Model\Concerns;

use Hyperf\Stringable\Str;

trait HasUuids
{
    use HasUniqueStringIds;

    /**
     * Generate a new UUID for the model.
     *
     * @return string
     */
    public function newUniqueId()
    {
        return (string) Str::uuidv7();
    }

    /**
     * Determine if given key is valid.
     *
     * @param mixed $value
     */
    protected function isValidUniqueId($value): bool
    {
        return Str::isUuid($value);
    }
}
