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

use Hyperf\Di\LazyLoader\LazyProxyTrait;

class LazyProxy extends Proxied
{
    use LazyProxyTrait;

    public const PROXY_TARGET = 'HyperfTest\Di\Stub\Proxied';
}
