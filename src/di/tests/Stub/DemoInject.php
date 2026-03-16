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

namespace HyperfTest\Di\Stub;

use Hyperf\Di\Annotation\Inject;

class DemoInject
{
    #[Inject]
    private Demo $demo;

    #[Inject(required: false)]
    private ?Demo1 $demo1 = null;

    public function getDemo()
    {
        return $this->demo;
    }

    public function getDemo1()
    {
        return $this->demo1;
    }
}
