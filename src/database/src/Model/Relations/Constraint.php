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

namespace Hyperf\Database\Model\Relations;

use Hyperf\Context\Context;
use Hyperf\Engine\Extension;

class Constraint
{
    protected static $constraint = true;

    /**
     * Indicates if the relation is adding constraints.
     */
    public static function isConstraint(): bool
    {
        if (Extension::isLoaded()) {
            return (bool) Context::get(static::class . '::isConstraint', true);
        }

        return static::$constraint;
    }

    public static function setConstraint(bool $constraint): bool
    {
        if (Extension::isLoaded()) {
            return Context::set(static::class . '::isConstraint', $constraint);
        }

        return static::$constraint = $constraint;
    }
}
