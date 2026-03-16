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
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Crontab\Annotation\Crontab as CrontabAnnotation;
use Hyperf\Crontab\CrontabManager;
use Hyperf\Crontab\Listener\CrontabRegisterListener;
use Hyperf\Crontab\Parser;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Container;
use Hyperf\Support\Reflection\ClassInvoker;
use HyperfTest\Crontab\Stub\FooCron;
use HyperfTest\Crontab\Stub\FooCron2;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class CrontabRegisterListenerTest extends TestCase
{
    protected $prevContainer;

    protected function setUp(): void
    {
        if (ApplicationContext::hasContainer()) {
            $this->prevContainer = ApplicationContext::getContainer();
        }
    }

    protected function tearDown(): void
    {
        if ($this->prevContainer instanceof ContainerInterface) {
            ApplicationContext::setContainer($this->prevContainer);
        }
    }

    public function testBuildCrontabWithoutConstructorByAnnotationEnableMethod()
    {
        $container = Mockery::mock(Container::class);
        $container->shouldReceive('has')->with(FooCron2::class)->andReturnTrue();
        $container->shouldReceive('get')->with(FooCron2::class)->andReturn(new FooCron2());
        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturn(Mockery::mock(StdoutLoggerInterface::class));
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(Mockery::mock(ConfigInterface::class));
        ApplicationContext::setContainer($container);

        $crontabAnnotation = new Crontab();
        $crontabAnnotation->callback = 'execute';
        $crontabAnnotation->enable = 'isEnable';
        $crontabAnnotation->collectClass(FooCron2::class);

        $annotationCrontabs = AnnotationCollector::getClassesByAnnotation(CrontabAnnotation::class);

        $manager = new CrontabManager(new Parser());
        $container->shouldReceive('get')->with(CrontabManager::class)->andReturn($manager);
        $class = new ClassInvoker(new CrontabRegisterListener($container));

        $crontab = $class->buildCrontabByAnnotation($annotationCrontabs[FooCron2::class]);

        $this->assertTrue($crontab->isEnable());
    }

    public function testBuildCrontabWithConstructorByAnnotationEnableMethod()
    {
        $container = Mockery::mock(Container::class);
        $container->shouldReceive('has')->with(FooCron::class)->andReturnTrue();
        $container->shouldReceive('get')->with(FooCron::class)->andReturn(new FooCron(new Config([
            'enable' => true,
        ])));
        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturn(Mockery::mock(StdoutLoggerInterface::class));
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(Mockery::mock(ConfigInterface::class));
        ApplicationContext::setContainer($container);

        $crontabAnnotation = new Crontab();
        $crontabAnnotation->callback = 'execute';
        $crontabAnnotation->enable = 'isEnable';
        $crontabAnnotation->collectClass(FooCron::class);

        $annotationCrontabs = AnnotationCollector::getClassesByAnnotation(CrontabAnnotation::class);

        $manager = new CrontabManager(new Parser());
        $container->shouldReceive('get')->with(CrontabManager::class)->andReturn($manager);
        $class = new ClassInvoker(new CrontabRegisterListener($container));

        $crontab = $class->buildCrontabByAnnotation($annotationCrontabs[FooCron::class]);

        $this->assertTrue($crontab->isEnable());
    }

    public function testBuildCrontabWithStaticMethodByAnnotationEnableMethod()
    {
        $container = Mockery::mock(Container::class);
        $container->shouldReceive('has')->with(FooCron::class)->andReturnTrue();
        $container->shouldReceive('get')->with(FooCron::class)->andReturn(new FooCron(new Config([
            'enable' => true,
        ])));
        $container->shouldReceive('get')->with(StdoutLoggerInterface::class)->andReturn(Mockery::mock(StdoutLoggerInterface::class));
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(Mockery::mock(ConfigInterface::class));
        ApplicationContext::setContainer($container);

        $crontabAnnotation = new Crontab();
        $crontabAnnotation->callback = 'execute';
        $crontabAnnotation->enable = 'isEnableCrontab';
        $crontabAnnotation->collectClass(FooCron::class);

        $annotationCrontabs = AnnotationCollector::getClassesByAnnotation(CrontabAnnotation::class);

        $manager = new CrontabManager(new Parser());
        $container->shouldReceive('get')->with(CrontabManager::class)->andReturn($manager);
        $class = new ClassInvoker(new CrontabRegisterListener($container));
        $crontab = $class->buildCrontabByAnnotation($annotationCrontabs[FooCron::class]);

        $this->assertTrue($crontab->isEnable());
    }
}
