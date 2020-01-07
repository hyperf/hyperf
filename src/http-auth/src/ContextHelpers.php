<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\HttpAuth;

use Hyperf\Utils\Context;

trait ContextHelpers
{
    public function setContext($id, $value)
    {
        $id = static::class . '::' . $id;
        Context::set($id, $value);
        return $value;
    }

    public function getContext($id, $default = null, $coroutineId = null)
    {
        $id = static::class . '::' . $id;
        return Context::get($id, $default, $coroutineId);
    }
}
