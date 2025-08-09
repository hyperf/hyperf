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

namespace HyperfTest\Di\Stub\Bind;

use Hyperf\Di\Annotation\BindTo;

#[BindTo(TestServiceInterface::class)]
class TestBindToClass implements TestServiceInterface
{
    public function process(): string
    {
        return 'test bind to class';
    }
}
