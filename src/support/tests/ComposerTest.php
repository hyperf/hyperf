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
namespace HyperfTest\Support;

use Composer\Autoload\ClassLoader;
use Hyperf\Support\Composer;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ComposerTest extends TestCase
{
    public function testFindLoader()
    {
        $loader = Composer::getLoader();

        $this->assertInstanceOf(ClassLoader::class, $loader);
    }
}
