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

namespace HyperfTest\Di\ExceptionStub;

use Hyperf\Di\Annotation\Inject;

/**
 * Class DemoInject.
 */
class DemoInjectException
{
    /**
     * @var Demo1
     */
    #[Inject(required: true)]
    private $demo;

    public function getDemo()
    {
        return $this->demo;
    }
}
