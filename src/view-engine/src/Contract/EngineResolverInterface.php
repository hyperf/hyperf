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

namespace Hyperf\ViewEngine\Contract;

use Closure;

interface EngineResolverInterface
{
    public function register(string $engine, Closure $resolver);

    public function resolve(string $engine): EngineInterface;
}
