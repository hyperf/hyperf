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

namespace HyperfTest\Config;

use Hyperf\Config\Config;
use Hyperf\Config\ConfigFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ConfigFactoryTest extends TestCase
{
    public function testConfigFactory()
    {
        $path = __DIR__ . '/Stub/autoload';

        /** @phpstan-ignore-next-line */
        $autoloadConfig = (fn () => $this->readPaths([$path]))->call(new ConfigFactory());

        $config = new Config(array_merge_recursive(...$autoloadConfig));

        $this->assertSame([
            'name' => 'apple@root',
        ], $config->get('apple'));

        $this->assertSame([
            'name' => 'apple@a',
        ], $config->get('a.apple'));

        $this->assertSame([
            'name' => 'apple@b',
            'weight' => '1.5kg',
        ], $config->get('b.apple'));

        $this->assertSame([
            'pear' => [
                'name' => 'pear@root',
            ],
        ], $config->get('fruit'));

        $this->assertSame([
            'name' => 'pear@root',
        ], $config->get('fruit.pear'));

        $this->assertSame([
            'name' => 'banana',
        ], $config->get('a.c.banana'));

        $this->assertSame('banana', $config->get('a.c.banana.name'));

        $this->assertEquals([
            'id' => 'c',
            'banana' => [
                'name' => 'banana',
            ],
        ], $config->get('a.c'));
    }
}
