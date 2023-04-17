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

use Hyperf\Di\Definition\PriorityDefinition;
use HyperfTest\Config\Stub\ProviderConfig;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class PriorityDefinitionTest extends TestCase
{
    public function testProviderConfigLoad()
    {
        $result = ProviderConfig::merge(
            [
                'dependencies' => [
                    'foo' => 'foo1',
                ],
            ],
            [
                'dependencies' => [
                    'foo' => 'foo2',
                ],
            ]
        );

        $this->assertSame('foo2', $result['dependencies']['foo']);

        $result = ProviderConfig::merge(
            [
                'dependencies' => [
                    'foo' => new PriorityDefinition('foo', 1),
                ],
            ],
            [
                'dependencies' => [
                    'foo' => 'foo2',
                ],
            ]
        );

        $this->assertSame('foo', $result['dependencies']['foo']->getDefinition());

        $result = ProviderConfig::merge(
            [
                'dependencies' => [
                    'foo' => new PriorityDefinition('foo', 2),
                    'bar' => 'bar1',
                ],
            ],
            [
                'dependencies' => [
                    'foo' => new PriorityDefinition('foo3', 1),
                    'bar' => 'bar2',
                ],
            ]
        );

        $this->assertSame('foo', $result['dependencies']['foo']->getDefinition());
        $this->assertSame('bar2', $result['dependencies']['bar']);
    }
}
