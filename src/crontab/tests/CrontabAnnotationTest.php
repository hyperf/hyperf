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
namespace HyperfTest\Crontab;

use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Di\Container;
use Hyperf\Utils\ApplicationContext;
use HyperfTest\Crontab\Stub\FooCron;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class CrontabAnnotationTest extends TestCase
{
    public function testIsEnable()
    {
        $container = Mockery::mock(Container::class);
        $container->shouldReceive('get')->once()->with(ConfigInterface::class)->andReturn(new Config([
            'enable' => false,
        ]));
        $container->shouldReceive('make')->once()->with(FooCron::class, [])->andReturn(new FooCron(
            $container->get(ConfigInterface::class)
        ));

        ApplicationContext::setContainer($container);

        $annotation = new Crontab();
        $annotation->collectClass(FooCron::class);

        $this->assertFalse($annotation->enable);
        $this->assertEquals([FooCron::class, 'execute'], $annotation->callback);
    }
}
