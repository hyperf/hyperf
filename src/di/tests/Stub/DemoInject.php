<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE.md
 */

namespace HyperfTest\Di\Stub;

use Hyperf\Di\Annotation\Inject;

class DemoInject
{
    /**
     * @Inject
     * @var Demo
     */
    private $demo;

    /**
     * @Inject(require=false)
     * @var Demo1
     */
    private $demo1;

    public function getDemo()
    {
        return $this->demo;
    }

    public function getDemo1()
    {
        return $this->demo1;
    }
}
