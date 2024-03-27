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

namespace HyperfTest\Di\Stub\Inject;

use Hyperf\Di\Annotation\Inject;

class OriginClass extends ParentClass
{
    use FooTrait;

    /**
     * @var Tar
     */
    #[Inject]
    protected $foo;

    public function getFoo()
    {
        return $this->foo;
    }
}
