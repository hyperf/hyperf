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

namespace HyperfTest\Di\ExceptionStub;

use Hyperf\Di\Annotation\Inject;

/**
 * Class DemoInject.
 */
class DemoInjectException
{
    /**
     * @Inject(require=true)
     * @var Demo1
     */
    private $demo;

    public function getDemo()
    {
        return $this->demo;
    }
}
