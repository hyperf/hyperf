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

namespace HyperfTest\Amqp\Listener;

use Hyperf\Amqp\ConsumerManager;
use Hyperf\Amqp\Listener\BeforeMainServerStartListener;
use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class BeforeMainServerStartListenerTest extends TestCase
{
    /**
     * Tear down the test case.
     */
    public function tearDown(): void
    {
        Mockery::close();
    }

    public function testProcessWithDisabled()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('has')->with(ConfigInterface::class)->andReturnTrue();
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturnUsing(function () {
            return new Config([
                'amqp' => [
                    'enable' => false,
                ],
            ]);
        });

        // If disabled, the ConsumerManager class will never be fetched.
        $container->shouldReceive('get')->with(ConsumerManager::class)->andReturnUsing(function () use ($container) {
            return new ConsumerManager($container);
        })->never();

        $listener = new BeforeMainServerStartListener($container);
        $listener->process(new stdClass());

        $this->assertTrue(true);
    }

    public function testProcessEnableDontHaveConfig()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('has')->with(ConfigInterface::class)->andReturnFalse();

        $this->checkEnable($container);

        $listener = new BeforeMainServerStartListener($container);
        $listener->process(new stdClass());

        $this->assertTrue(true);
    }

    public function testProcessEnableByTrue()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('has')->with(ConfigInterface::class)->andReturnTrue();
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturnUsing(function () {
            return new Config([
                'amqp' => [
                    'enable' => true,
                ],
            ]);
        });

        $this->checkEnable($container);

        $listener = new BeforeMainServerStartListener($container);
        $listener->process(new stdClass());

        $this->assertTrue(true);
    }

    public function testProcessEnableByNoValue()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('has')->with(ConfigInterface::class)->andReturnTrue();
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturnUsing(function () {
            return new Config([]);
        });

        $this->checkEnable($container);

        $listener = new BeforeMainServerStartListener($container);
        $listener->process(new stdClass());

        $this->assertTrue(true);
    }

    protected function checkEnable(ContainerInterface $container)
    {
        $container->shouldReceive('get')->with(ConsumerManager::class)->andReturn($manager = Mockery::mock(ConsumerManager::class));

        $manager->shouldReceive('run')->andReturnNull()->once();
    }
}
