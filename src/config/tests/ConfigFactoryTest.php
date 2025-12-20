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
use ReflectionMethod;

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

    public function testMergeTwoPreservesScalarValues()
    {
        $base = [
            'database' => [
                'connections' => [
                    'mysql' => [
                        'driver' => 'mysql',
                        'host' => 'localhost',
                    ],
                ],
            ],
        ];

        $override = [
            'database' => [
                'connections' => [
                    'mysql' => [
                        'driver' => 'mysql',
                        'port' => 3306,
                    ],
                ],
            ],
        ];

        $result = $this->callMergeTwo($base, $override);

        $this->assertIsString($result['database']['connections']['mysql']['driver']);
        $this->assertSame('mysql', $result['database']['connections']['mysql']['driver']);
        $this->assertSame('localhost', $result['database']['connections']['mysql']['host']);
        $this->assertSame(3306, $result['database']['connections']['mysql']['port']);
    }

    public function testMergeTwoCombinesLists()
    {
        $base = ['commands' => ['CommandA', 'CommandB']];
        $override = ['commands' => ['CommandC']];

        $result = $this->callMergeTwo($base, $override);

        $this->assertSame(['CommandA', 'CommandB', 'CommandC'], $result['commands']);
    }

    public function testMergeTwoDeduplicatesLists()
    {
        $base = ['commands' => ['CommandA', 'CommandB']];
        $override = ['commands' => ['CommandB', 'CommandC']];

        $result = $this->callMergeTwo($base, $override);

        $this->assertSame(['CommandA', 'CommandB', 'CommandC'], $result['commands']);
    }

    public function testMergeTwoPreservesListenersWithPriority()
    {
        $base = ['listeners' => ['ListenerA', 'ListenerB']];
        $override = ['listeners' => ['ListenerC', 'PriorityListener' => 99]];

        $result = $this->callMergeTwo($base, $override);

        $this->assertContains('ListenerA', $result['listeners']);
        $this->assertContains('ListenerB', $result['listeners']);
        $this->assertContains('ListenerC', $result['listeners']);
        $this->assertArrayHasKey('PriorityListener', $result['listeners']);
        $this->assertSame(99, $result['listeners']['PriorityListener']);
    }

    private function callMergeTwo(array $base, array $override): array
    {
        $factory = new ConfigFactory();
        $method = new ReflectionMethod($factory, 'mergeTwo');

        return $method->invoke($factory, $base, $override);
    }
}
