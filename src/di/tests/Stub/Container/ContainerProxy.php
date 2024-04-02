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

namespace HyperfTest\Di\Stub\Container;

use Hyperf\Di\Container;

class ContainerProxy extends Container implements Container1HasInterface, Container2HasInterface
{
    public function __construct()
    {
    }
}
