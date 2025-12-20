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

    public function testMergeThreeConfigsPreservesScalarValues()
    {
        $c1 = ['app' => ['name' => 'First', 'debug' => false]];
        $c2 = ['app' => ['name' => 'Second', 'timezone' => 'UTC']];
        $c3 = ['app' => ['name' => 'Third', 'debug' => true]];

        $result = ProviderConfig::merge($c1, $c2, $c3);

        $this->assertIsString($result['app']['name']);
        $this->assertSame('Third', $result['app']['name']);
        $this->assertIsBool($result['app']['debug']);
        $this->assertTrue($result['app']['debug']);
        $this->assertSame('UTC', $result['app']['timezone']);
    }

    public function testMergeWithNoArraysReturnsEmpty()
    {
        $result = ProviderConfig::merge();

        $this->assertSame([], $result);
    }

    public function testMergeSingleArrayReturnsUnchanged()
    {
        $config = [
            'app' => ['name' => 'MyApp', 'debug' => true],
            'commands' => ['CommandA', 'CommandB'],
        ];

        $result = ProviderConfig::merge($config);

        $this->assertSame($config, $result);
    }

    public function testMergeHandlesNullValues()
    {
        $c1 = ['app' => ['value' => 'not_null', 'nullable' => null]];
        $c2 = ['app' => ['value' => null, 'nullable' => 'now_has_value']];

        $result = ProviderConfig::merge($c1, $c2);

        $this->assertNull($result['app']['value']);
        $this->assertSame('now_has_value', $result['app']['nullable']);
    }

    public function testMergeMixedNumericAndStringKeys()
    {
        $c1 = ['mixed' => ['numeric_0', 'string_key' => 'value_a', 'numeric_1']];
        $c2 = ['mixed' => ['another_numeric', 'string_key' => 'value_b']];

        $result = ProviderConfig::merge($c1, $c2);

        $this->assertSame('value_b', $result['mixed']['string_key']);
        $this->assertContains('numeric_0', $result['mixed']);
        $this->assertContains('numeric_1', $result['mixed']);
        $this->assertContains('another_numeric', $result['mixed']);
        $this->assertCount(4, $result['mixed']);
    }

    public function testMergeScalarReplacesArray()
    {
        $c1 = ['setting' => ['complex' => 'array', 'with' => 'values']];
        $c2 = ['setting' => 'simple_string'];

        $result = ProviderConfig::merge($c1, $c2);

        $this->assertIsString($result['setting']);
        $this->assertSame('simple_string', $result['setting']);
    }

    public function testMergeNestedNumericArraysWithinAssociative()
    {
        $c1 = [
            'annotations' => [
                'scan' => [
                    'paths' => ['/path/a', '/path/b'],
                    'collectors' => ['CollectorA'],
                ],
            ],
        ];

        $c2 = [
            'annotations' => [
                'scan' => [
                    'paths' => ['/path/c'],
                    'ignore_annotations' => ['IgnoreMe'],
                ],
            ],
        ];

        $result = ProviderConfig::merge($c1, $c2);

        $this->assertSame(['/path/a', '/path/b', '/path/c'], $result['annotations']['scan']['paths']);
        $this->assertSame(['CollectorA'], $result['annotations']['scan']['collectors']);
        $this->assertSame(['IgnoreMe'], $result['annotations']['scan']['ignore_annotations']);
    }

    public function testMergePreservesBooleanTypes()
    {
        $c1 = ['flags' => ['enabled' => true, 'verbose' => false]];
        $c2 = ['flags' => ['enabled' => false, 'debug' => true]];

        $result = ProviderConfig::merge($c1, $c2);

        $this->assertIsBool($result['flags']['enabled']);
        $this->assertFalse($result['flags']['enabled']);
        $this->assertIsBool($result['flags']['verbose']);
        $this->assertFalse($result['flags']['verbose']);
        $this->assertIsBool($result['flags']['debug']);
        $this->assertTrue($result['flags']['debug']);
    }

    public function testMergePreservesIntegerTypes()
    {
        $c1 = ['limits' => ['timeout' => 30, 'retries' => 3]];
        $c2 = ['limits' => ['timeout' => 60, 'max_connections' => 100]];

        $result = ProviderConfig::merge($c1, $c2);

        $this->assertIsInt($result['limits']['timeout']);
        $this->assertSame(60, $result['limits']['timeout']);
        $this->assertIsInt($result['limits']['retries']);
        $this->assertSame(3, $result['limits']['retries']);
        $this->assertIsInt($result['limits']['max_connections']);
        $this->assertSame(100, $result['limits']['max_connections']);
    }

    public function testMergeRealWorldDatabaseConfigScenario()
    {
        $coreConfig = [
            'database' => [
                'default' => 'sqlite',
                'connections' => [
                    'sqlite' => [
                        'driver' => 'sqlite',
                        'database' => '/app/database.sqlite',
                    ],
                    'pgsql' => [
                        'driver' => 'pgsql',
                        'host' => 'localhost',
                        'port' => 5432,
                    ],
                ],
            ],
        ];

        $databaseConfig = [
            'database' => [
                'connections' => [
                    'sqlite' => [
                        'driver' => 'sqlite',
                        'foreign_key_constraints' => true,
                    ],
                ],
            ],
        ];

        $mysqlConfig = [
            'database' => [
                'connections' => [
                    'mysql' => [
                        'driver' => 'mysql',
                        'host' => 'localhost',
                        'port' => 3306,
                    ],
                ],
            ],
        ];

        $result = ProviderConfig::merge($coreConfig, $databaseConfig, $mysqlConfig);

        $this->assertIsString($result['database']['connections']['sqlite']['driver']);
        $this->assertIsString($result['database']['connections']['pgsql']['driver']);
        $this->assertIsString($result['database']['connections']['mysql']['driver']);
        $this->assertSame('sqlite', $result['database']['connections']['sqlite']['driver']);
        $this->assertSame('pgsql', $result['database']['connections']['pgsql']['driver']);
        $this->assertSame('mysql', $result['database']['connections']['mysql']['driver']);
        $this->assertSame('/app/database.sqlite', $result['database']['connections']['sqlite']['database']);
        $this->assertTrue($result['database']['connections']['sqlite']['foreign_key_constraints']);
        $this->assertSame(5432, $result['database']['connections']['pgsql']['port']);
        $this->assertSame(3306, $result['database']['connections']['mysql']['port']);
    }
}
