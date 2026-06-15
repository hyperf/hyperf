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

namespace HyperfTest\Context\Stub;

/**
 * @internal
 */
class ContextMixin
{
    public function mixinMethod()
    {
        return function (string $value) {
            return 'mixin-' . $value;
        };
    }

    protected function protectedMixinMethod()
    {
        return function (string $value) {
            return 'protected-' . $value;
        };
    }
}
