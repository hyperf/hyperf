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

namespace HyperfTest\Rpn\Stub;

use Hyperf\Rpn\Operator\HasBindings;

class HasBindingsStub
{
    use HasBindings;

    public function getValue($string)
    {
        return $this->getBindingIndex($string);
    }
}
