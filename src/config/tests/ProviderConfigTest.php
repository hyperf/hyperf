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

use Hyperf\Collection\Arr;
use HyperfTest\Config\Stub\FooConfigProvider;
use HyperfTest\Config\Stub\ProviderConfig;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

use function Hyperf\Support\value;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ProviderConfigTest extends TestCase
{
    public function testProviderConfigMerge()
    {
        $c1 = [
            'listeners' => ['L1'],
            'dependencies' => [
                'D1' => 'D1',
                'D2' => 'D2',
            ],
        ];

        $c2 = [
            'listeners' => ['L2'],
            'dependencies' => [
                'D1' => 'D1',
                'D2' => 'D3',
            ],
        ];

        $c3 = [
            'listeners' => ['L2'],
            'dependencies' => [
                'D1' => 'D1',
                'D3' => 'D3',
                'D4' => 'D4',
            ],
        ];

        $result = ProviderConfig::merge($c1, $c2, $c3);

        $this->assertSame(['D1' => 'D1', 'D2' => 'D3', 'D3' => 'D3', 'D4' => 'D4'], $result['dependencies']);
    }

    public function testProviderConfigNotHaveDependencies()
    {
        $c1 = [
            'listeners' => ['L1'],
            'dependencies' => [
                'D1' => 'D1',
                'D2' => 'D2',
            ],
        ];

        $c2 = [
            'listeners' => ['L2'],
        ];

        $result = ProviderConfig::merge($c1, $c2);
        $this->assertSame(['D1' => 'D1', 'D2' => 'D2'], $result['dependencies']);
        $this->assertSame(['L1', 'L2'], $result['listeners']);
    }

    public function testProviderConfigHaveNull()
    {
        $c1 = [
            'listeners' => ['L1'],
        ];

        $c2 = [
            'listeners' => [value(function () {
                return null;
            })],
        ];

        $result = ProviderConfig::merge($c1, $c2);
        $this->assertSame(['L1', null], $result['listeners']);
    }

    public function testProviderConfigLoadProviders()
    {
        $config = json_decode(file_get_contents(BASE_PATH . '/composer.json'), true);

        $providers = $config['extra']['hyperf']['config'];

        $res = ProviderConfig::loadProviders($providers);

        $dependencies = $res['dependencies'];
        $commands = $res['commands'];
        $scanPaths = $res['annotations']['scan']['paths'];
        $publish = $res['publish'];
        $listeners = $res['listeners'];
        $processes = $res['processes'];

        $this->assertFalse(Arr::isAssoc($commands));
        $this->assertFalse(Arr::isAssoc($scanPaths));
        $this->assertTrue(Arr::isAssoc($listeners));
        $this->assertFalse(Arr::isAssoc($publish));
        $this->assertFalse(Arr::isAssoc($processes));
        $this->assertTrue(Arr::isAssoc($dependencies));
    }

    public function testProviderConfigLoadProvidersHasCallable()
    {
        $res = ProviderConfig::loadProviders([
            FooConfigProvider::class,
        ]);

        foreach ($res['dependencies'] as $dependency) {
            $this->assertTrue(is_string($dependency) || is_callable($dependency));
        }
    }

    public function testMergePreservesScalarValues()
    {
        $c1 = [
            'database' => [
                'default' => 'mysql',
                'connections' => [
                    'mysql' => [
                        'driver' => 'mysql',
                        'host' => 'localhost',
                    ],
                ],
            ],
        ];

        $c2 = [
            'database' => [
                'connections' => [
                    'mysql' => [
                        'driver' => 'mysql',
                        'port' => 3306,
                    ],
                ],
            ],
        ];

        $result = ProviderConfig::merge($c1, $c2);

        $this->assertIsString($result['database']['connections']['mysql']['driver']);
        $this->assertSame('mysql', $result['database']['connections']['mysql']['driver']);
        $this->assertSame('localhost', $result['database']['connections']['mysql']['host']);
        $this->assertSame(3306, $result['database']['connections']['mysql']['port']);
    }

    public function testMergePreservesListenersWithPriority()
    {
        $c1 = [
            'listeners' => [
                'ListenerA',
                'ListenerB',
            ],
        ];

        $c2 = [
            'listeners' => [
                'ListenerC',
                'PriorityListener' => 99,
            ],
        ];

        $result = ProviderConfig::merge($c1, $c2);

        $this->assertContains('ListenerA', $result['listeners']);
        $this->assertContains('ListenerB', $result['listeners']);
        $this->assertContains('ListenerC', $result['listeners']);
        $this->assertArrayHasKey('PriorityListener', $result['listeners']);
        $this->assertSame(99, $result['listeners']['PriorityListener']);
    }

    public function testMergeDeduplicatesLists()
    {
        $c1 = [
            'commands' => ['CommandA', 'CommandB'],
        ];

        $c2 = [
            'commands' => ['CommandB', 'CommandC'],
        ];

        $result = ProviderConfig::merge($c1, $c2);

        $this->assertSame(['CommandA', 'CommandB', 'CommandC'], $result['commands']);
    }

    public function testMergeDeeplyNestedConfigs()
    {
        $c1 = [
            'cache' => [
                'stores' => [
                    'redis' => [
                        'driver' => 'redis',
                        'connection' => 'default',
                    ],
                ],
            ],
        ];

        $c2 = [
            'cache' => [
                'stores' => [
                    'redis' => [
                        'prefix' => 'app_',
                    ],
                    'file' => [
                        'driver' => 'file',
                    ],
                ],
            ],
        ];

        $result = ProviderConfig::merge($c1, $c2);

        $this->assertSame('redis', $result['cache']['stores']['redis']['driver']);
        $this->assertSame('default', $result['cache']['stores']['redis']['connection']);
        $this->assertSame('app_', $result['cache']['stores']['redis']['prefix']);
        $this->assertSame('file', $result['cache']['stores']['file']['driver']);
    }
}
