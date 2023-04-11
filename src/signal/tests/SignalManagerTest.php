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
namespace HyperfTest\Signal;

use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Signal\SignalHandlerInterface as SignalHandler;
use Hyperf\Signal\SignalManager;
use HyperfTest\Signal\Stub\SignalHandler2Stub;
use HyperfTest\Signal\Stub\SignalHandlerStub;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class SignalManagerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        Context::set('test.signal', null);
    }

    public function testGetHandlers()
    {
        $container = $this->getContainer();
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturnUsing(function () {
            return new Config([
                'signal' => [
                    'handlers' => [
                        SignalHandlerStub::class,
                        SignalHandler2Stub::class => 1,
                    ],
                ],
            ]);
        });
        $manager = new SignalManager($container);
        $manager->init();

        $this->assertArrayHasKey(SignalHandler::WORKER, $manager->getHandlers());
        $this->assertArrayHasKey(SIGTERM, $manager->getHandlers()[SignalHandler::WORKER]);
        $this->assertIsArray($manager->getHandlers()[SignalHandler::WORKER]);
        $this->assertInstanceOf(SignalHandler2Stub::class, $manager->getHandlers()[SignalHandler::WORKER][SIGTERM][0]);
        $this->assertInstanceOf(SignalHandlerStub::class, $manager->getHandlers()[SignalHandler::WORKER][SIGTERM][1]);
    }

    protected function getContainer()
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        $container->shouldReceive('get')->with(SignalHandlerStub::class)->andReturnUsing(function () {
            return new SignalHandlerStub();
        });
        $container->shouldReceive('get')->with(SignalHandler2Stub::class)->andReturnUsing(function () {
            return new SignalHandler2Stub();
        });

        return $container;
    }
}
