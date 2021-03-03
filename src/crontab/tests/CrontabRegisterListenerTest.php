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
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Crontab\Annotation\Crontab as CrontabAnnotation;
use Hyperf\Crontab\CrontabManager;
use Hyperf\Crontab\Listener\CrontabRegisterListener;
use Hyperf\Crontab\Parser;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Container;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Reflection\ClassInvoker;
use HyperfTest\Crontab\Stub\FooCron;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class CrontabRegisterListenerTest extends TestCase
{
    public function testBuildCrontabByAnnotationEnableMethod()
    {
        $container = Mockery::mock(Container::class);
        $container->shouldReceive('get')->once()->with(ConfigInterface::class)->andReturn(new Config([
            'enable' => false,
        ]));
        $container->shouldReceive('make')->once()->with(FooCron::class, [])->andReturn(new FooCron(
            $container->get(ConfigInterface::class)
        ));
        ApplicationContext::setContainer($container);

        $crontabAnnotation = new Crontab();
        $crontabAnnotation->enableMethod = 'isEnable';
        $crontabAnnotation->collectClass(FooCron::class);

        $annotationCrontabs = AnnotationCollector::getClassesByAnnotation(CrontabAnnotation::class);

        $manager = new CrontabManager(new Parser());
        $class = new ClassInvoker(new CrontabRegisterListener($manager, Mockery::mock(StdoutLoggerInterface::class), Mockery::mock(ConfigInterface::class)));

        $crontab = $class->buildCrontabByAnnotation(FooCron::class, $annotationCrontabs[FooCron::class]);

        $this->assertFalse($crontab->isEnable());
    }
}
