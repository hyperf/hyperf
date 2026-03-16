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

namespace HyperfTest\ViewEngine\Blade;

use Hyperf\Support\Filesystem\Filesystem;
use Hyperf\ViewEngine\Compiler\BladeCompiler;
use Mockery as m;
use PHPUnit\Framework\TestCase;

abstract class AbstractBladeTestCase extends TestCase
{
    protected BladeCompiler $compiler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->compiler = new BladeCompiler($this->getFiles(), __DIR__ . '../storage/cache/');
    }

    protected function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    protected function getFiles()
    {
        $filesystem = m::mock(Filesystem::class);
        $filesystem->shouldReceive('exists')->andReturn(true);
        return $filesystem;
    }
}
