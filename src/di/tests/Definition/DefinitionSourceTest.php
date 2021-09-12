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
namespace HyperfTest\Di\Definition;

use Hyperf\Di\Definition\DefinitionSource;
use HyperfTest\Di\Stub\Bar;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class DefinitionSourceTest extends TestCase
{
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testGetDefinition()
    {
        $source = new DefinitionSource([]);
        $source->getDefinition(Bar::class);
        $this->assertSame(1, count($source->getDefinitions()));
    }
}
