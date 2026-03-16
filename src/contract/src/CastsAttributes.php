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

namespace Hyperf\Contract;

interface CastsAttributes
{
    /**
     * Transform the attribute from the underlying model values.
     *
     * @param object $model
     * @param mixed $value
     * @return mixed
     */
    public function get($model, string $key, $value, array $attributes);

    /**
     * Transform the attribute to its underlying model values.
     *
     * @param object $model
     * @param mixed $value
     * @return array|string
     */
    public function set($model, string $key, $value, array $attributes);
}
