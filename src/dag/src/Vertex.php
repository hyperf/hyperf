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

namespace Hyperf\Dag;

use Closure;

class Vertex
{
    public ?string $key = null;

    /**
     * @var callable
     */
    public $value;

    /**
     * @var array<Vertex>
     */
    public array $parents = [];

    /**
     * @var array<Vertex>
     */
    public array $children = [];

    public static function make(callable $job, ?string $key = null): self
    {
        $closure = Closure::fromCallable($job);
        if ($key === null) {
            $key = spl_object_hash($closure);
        }

        $v = new Vertex();
        $v->key = $key;
        $v->value = $closure;
        return $v;
    }

    public static function of(Runner $job, ?string $key = null): self
    {
        if ($key === null) {
            $key = spl_object_hash($job);
        }

        $v = new Vertex();
        $v->key = $key;
        $v->value = [$job, 'run'];
        return $v;
    }
}
