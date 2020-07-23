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
namespace HyperfTest\ModelCache;

use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Collector\TableCollector;
use Hyperf\Utils\ApplicationContext;
use HyperfTest\ModelCache\Stub\ManagerStub;
use HyperfTest\ModelCache\Stub\ModelStub;
use HyperfTest\ModelCache\Stub\NonHandler;
use HyperfTest\ModelCache\Stub\StdoutLogger;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 * @coversNothing
 */
class ManagerTest extends TestCase
{
    public function tearDown()
    {
        Mockery::close();
    }

    public function testFormatModel()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')->once()->with(StdoutLoggerInterface::class)->andReturn(new StdoutLogger());
        $container->shouldReceive('get')->once()->with(ConfigInterface::class)->andReturn(new Config($this->getConfig()));
        $container->shouldReceive('make')->with(ContainerInterface::class)->andReturn($container);
        $container->shouldReceive('get')->with(EventDispatcherInterface::class)->andReturn(null);
        $container->shouldReceive('get')->with(TableCollector::class)->andReturn(new TableCollector());

        ApplicationContext::setContainer($container);

        $manager = new ManagerStub($container);

        $model = new ModelStub();
        $json = ['id' => 1, 'name' => 'Hyperf'];
        $model->fill(['id' => 1, 'json_data' => $json, 'str' => null, 'float_num' => 0.1]);

        $this->assertSame(['id' => 1, 'json_data' => json_encode($json), 'str' => null, 'float_num' => 0.1], $model->getAttributes());
        $this->assertSame(['id' => 1, 'json_data' => ['id' => 1, 'name' => 'Hyperf'], 'str' => null, 'float_num' => 0.1], $model->toArray());
        $res = $manager->formatModel($model);

        $this->assertSame(['id' => 1, 'json_data' => json_encode($json), 'str' => null, 'float_num' => 0.1], $res);
    }

    protected function getConfig(): array
    {
        return [
            'databases' => [
                'default' => [
                    'cache' => [
                        'handler' => NonHandler::class,
                        'cache_key' => 'mc:%s:m:%s:%s:%s',
                        'prefix' => 'default',
                        'pool' => 'default',
                        'ttl' => 3600 * 24,
                        'empty_model_ttl' => 3600,
                        'load_script' => true,
                    ],
                ],
            ],
        ];
    }
}
