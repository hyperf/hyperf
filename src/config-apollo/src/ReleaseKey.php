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

namespace Hyperf\ConfigApollo;

use Hyperf\Utils\Traits\Container;

class ReleaseKey
{
    use Container;

    /**
     * @var array
     */
    protected static $container = [];
}
