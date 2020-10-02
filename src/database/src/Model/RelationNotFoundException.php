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
namespace Hyperf\Database\Model;

use RuntimeException;

class RelationNotFoundException extends RuntimeException
{
    /**
     * The name of the affected Model model.
     *
     * @var string
     */
    public $model;

    /**
     * The name of the relation.
     *
     * @var string
     */
    public $relation;

    /**
     * Create a new exception instance.
     *
     * @param string $relation
     * @param mixed $model
     * @return static
     */
    public static function make($model, $relation)
    {
        $class = get_class($model);

        $instance = new static("Call to undefined relationship [{$relation}] on model [{$class}].");

        $instance->model = $model;
        $instance->relation = $relation;

        return $instance;
    }
}
