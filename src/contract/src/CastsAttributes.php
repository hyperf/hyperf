<?php
declare(strict_types=1);

namespace Hyperf\Contract;


interface CastsAttributes
{
    /**
     * Transform the attribute from the underlying model values.
     *
     * @param  \Hyperf\Database\Model\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function get($model, string $key, $value, array $attributes);

    /**
     * Transform the attribute to its underlying model values.
     *
     * @param  \Hyperf\Database\Model\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return array|string
     */
    public function set($model, string $key, $value, array $attributes);
}