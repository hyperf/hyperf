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

namespace Hyperf\Database\Exception;

use RuntimeException;

class ClassMorphViolationException extends RuntimeException
{
    /**
     * The name of the affected Eloquent model.
     */
    public string $model;

    /**
     * Create a new exception instance.
     */
    public function __construct(object $model)
    {
        $class = get_class($model);

        parent::__construct("No morph map defined for model [{$class}].");

        $this->model = $class;
    }
}
