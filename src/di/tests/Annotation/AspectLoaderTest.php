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

namespace HyperfTest\Di\Annotation;

use Hyperf\Di\Annotation\AspectLoader;
use Hyperf\Di\Annotation\Inject;
use HyperfTest\Di\Stub\Aspect\Debug1Aspect;
use HyperfTest\Di\Stub\Aspect\DebugLoaderAspect;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class AspectLoaderTest extends TestCase
{
    public function testLoad()
    {
        [$classes, $annotations, $priority] = AspectLoader::load(Debug1Aspect::class);

        $this->assertSame(['Debug1AspectFoo'], $classes);
        $this->assertSame([], $annotations);
        $this->assertSame(null, $priority);

        [$classes, $annotations, $priority] = AspectLoader::load(DebugLoaderAspect::class);

        $this->assertSame(['Debug1AspectFoo'], $classes);
        $this->assertSame([Inject::class], $annotations);
        $this->assertSame(100, $priority);
    }
}
