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

use Hyperf\Di\Annotation\Bind;

#[Bind(TestBind::class)]
interface TestBindClass
{
    public function handle(): string;
}
