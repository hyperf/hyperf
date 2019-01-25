<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Database\Model;

class IgnoreOnTouch
{
    /**
     * The list of models classes that should not be affected with touch.
     *
     * @var array
     */
    public static $container = [];
}
