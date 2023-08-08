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

class InvalidCastException extends RuntimeException
{
    /**
     * The name of the affected model.
     */
    public string $model;

    /**
     * The name of the column.
     */
    public string $column;

    /**
     * The name of the cast type.
     */
    public string $castType;

    public function __construct(object $model, string $column, string $castType)
    {
        $class = get_class($model);

        parent::__construct("Call to undefined cast [{$castType}] on column [{$column}] in model [{$class}].");

        $this->model = $class;
        $this->column = $column;
        $this->castType = $castType;
    }
}
