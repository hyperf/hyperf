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
     * @param string $model the name of the affected model
     * @param string $column the name of the column
     * @param string $castType the name of the cast type
     */
    public function __construct(public string $model, public string $column, public string $castType)
    {
        parent::__construct("Call to undefined cast [{$castType}] on column [{$column}] in model [{$model}].");
    }
}
